<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendanceExport;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Worker;
use App\Models\WorkerAttendance;
use App\Models\Project;
use App\Support\CompanyContext;
use App\Support\NepaliDate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $data = $this->sheetContext($request);

        if ($request->ajax() || $request->boolean('partial')) {
            return view('admin.attendance._panel', $data);
        }

        return view('admin.attendance.index', $data);
    }

    public function export(Request $request)
    {
        $data = $this->sheetContext($request);
        $statusShort = [
            'present' => 'P',
            'half_day' => 'H',
            'leave' => 'L',
            'absent' => 'A',
        ];

        $rows = [];
        foreach ($data['workers'] as $worker) {
            $present = 0;
            $half = 0;
            $leave = 0;
            $absent = 0;
            $cells = [];
            foreach ($data['grid']['days'] as $day) {
                $status = $data['attendanceMap'][$worker->id][$day['ad_date']]['status'] ?? '';
                $cells[] = $statusShort[$status] ?? '';
                if ($status === 'present') {
                    $present++;
                } elseif ($status === 'half_day') {
                    $half++;
                } elseif ($status === 'leave') {
                    $leave++;
                } elseif ($status === 'absent') {
                    $absent++;
                }
            }
            $rows[] = [
                'name' => $worker->name,
                'project' => $worker->project?->name ?? '',
                'cells' => $cells,
                'present' => $present,
                'half' => $half,
                'leave' => $leave,
                'absent' => $absent,
                'total' => $present + ($half * 0.5),
            ];
        }

        $days = [];
        foreach ($data['grid']['days'] as $day) {
            $days[] = [
                'label' => (string) ($data['calendar'] === 'bs' ? $day['bs_label'] : $day['ad_label']),
                'weekday' => $day['weekday'],
                'weekend' => (bool) $day['is_weekend'],
            ];
        }

        $companyName = Company::find($data['companyId'])?->name;
        $title = trim(($companyName ? $companyName . ' — ' : '') . 'Attendance Report');
        $subtitle = $data['grid']['month_label'] . ' · ' . $data['dualLabel'];
        $summary = $data['summary'];
        $summaryLine = 'Present: ' . $summary['present']
            . '    Half: ' . $summary['half_day']
            . '    Leave: ' . $summary['leave']
            . '    Absent: ' . $summary['absent'];

        $filename = 'attendance-' . Str::slug($data['grid']['month_label']) . '.xlsx';

        return Excel::download(new AttendanceExport($title, $subtitle, $summaryLine, $days, $rows), $filename);
    }

    protected function sheetContext(Request $request): array
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $calendar = $request->get('calendar', 'bs') === 'ad' ? 'ad' : 'bs';
        $today = Carbon::today();

        if ($calendar === 'bs') {
            $bsToday = NepaliDate::adToBs($today);
            $year = (int) $request->get('year', $bsToday['year']);
            $month = (int) $request->get('month', $bsToday['month']);
            $year = max(NepaliDate::minBsYear(), min(NepaliDate::maxBsYear(), $year));
            $month = max(1, min(12, $month));
        } else {
            $year = (int) $request->get('year', $today->year);
            $month = (int) $request->get('month', $today->month);
            $month = max(1, min(12, $month));
        }

        $grid = NepaliDate::monthGrid($calendar, $year, $month);
        $projectId = $request->get('project_id');

        $workersQuery = Worker::where('company_id', $companyId)->where('is_active', true);
        if ($projectId) {
            $workersQuery->where(function ($q) use ($projectId) {
                $q->where('project_id', $projectId)->orWhereNull('project_id');
            });
        }
        $workers = $workersQuery->with('project')->orderBy('name')->get();

        $adDates = collect($grid['days'])->pluck('ad_date');
        $startDate = $adDates->first();
        $endDate = $adDates->last();

        $attendanceRows = WorkerAttendance::where('company_id', $companyId)
            ->whereIn('worker_id', $workers->pluck('id'))
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Map: worker_id => [ad_date => status]
        $attendanceMap = [];
        foreach ($attendanceRows as $row) {
            $attendanceMap[$row->worker_id][$row->date->toDateString()] = [
                'status' => $row->status,
                'overtime_hours' => (float) $row->overtime_hours,
            ];
        }

        $summary = [
            'present' => $attendanceRows->where('status', 'present')->count(),
            'absent' => $attendanceRows->where('status', 'absent')->count(),
            'half_day' => $attendanceRows->where('status', 'half_day')->count(),
            'leave' => $attendanceRows->where('status', 'leave')->count(),
            'total_cells' => $workers->count() * count($grid['days']),
            'marked' => $attendanceRows->count(),
        ];

        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();

        $prev = NepaliDate::shiftMonth($calendar, $year, $month, -1);
        $next = NepaliDate::shiftMonth($calendar, $year, $month, 1);

        $bsMonths = NepaliDate::MONTH_NAMES_EN;
        $adMonths = [];
        for ($m = 1; $m <= 12; $m++) {
            $adMonths[$m] = Carbon::create(2000, $m, 1)->format('F');
        }

        // Dual label for header (other calendar)
        $rangeStartBs = NepaliDate::adToBs($startDate);
        $rangeEndBs = NepaliDate::adToBs($endDate);
        $dualLabel = $calendar === 'ad'
            ? $rangeStartBs['label'] . ' – ' . $rangeEndBs['label'] . ' BS'
            : Carbon::parse($startDate)->format('M j, Y') . ' – ' . Carbon::parse($endDate)->format('M j, Y') . ' AD';

        return compact(
            'companyId',
            'workers',
            'attendanceMap',
            'projects',
            'projectId',
            'summary',
            'calendar',
            'year',
            'month',
            'grid',
            'prev',
            'next',
            'bsMonths',
            'adMonths',
            'dualLabel',
            'today'
        );
    }

    /**
     * Null if allowed; otherwise human-readable error.
     */
    protected function attendanceDateError(Worker $worker, string $date): ?string
    {
        $day = Carbon::parse($date)->startOfDay();
        $today = Carbon::today();

        if ($day->gt($today)) {
            return 'Cannot mark attendance for a future date.';
        }

        if ($worker->join_date) {
            $join = $worker->join_date->copy()->startOfDay();
            if ($day->lt($join)) {
                return 'Cannot mark attendance before join date (' . $join->format('Y-m-d') . ').';
            }
        }

        return null;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'entries' => 'required|array',
            'entries.*.worker_id' => 'required|exists:workers,id',
            'entries.*.date' => 'required|date',
            'entries.*.status' => 'nullable|in:present,absent,half_day,leave,',
            'project_id' => 'nullable|exists:projects,id',
            'calendar' => 'nullable|in:ad,bs',
            'year' => 'nullable|integer',
            'month' => 'nullable|integer',
        ]);

        $companyId = CompanyContext::getActiveCompanyId();
        $saved = 0;
        $cleared = 0;
        $skipped = 0;

        DB::transaction(function () use ($validated, $companyId, &$saved, &$cleared, &$skipped) {
            foreach ($validated['entries'] as $row) {
                $worker = Worker::where('company_id', $companyId)->find($row['worker_id']);
                if (! $worker) {
                    continue;
                }

                $date = Carbon::parse($row['date'])->toDateString();
                $status = $row['status'] ?? null;

                if ($status === null || $status === '') {
                    $deleted = WorkerAttendance::where('worker_id', $worker->id)
                        ->whereDate('date', $date)
                        ->delete();
                    $cleared += $deleted;
                    continue;
                }

                if ($this->attendanceDateError($worker, $date)) {
                    $skipped++;
                    continue;
                }

                WorkerAttendance::updateOrCreate(
                    [
                        'worker_id' => $worker->id,
                        'date' => $date,
                    ],
                    [
                        'company_id' => $companyId,
                        'project_id' => $validated['project_id'] ?? $worker->project_id,
                        'status' => $status,
                        'marked_by' => auth()->id(),
                    ]
                );
                $saved++;
            }
        });

        $params = array_filter([
            'calendar' => $validated['calendar'] ?? 'ad',
            'year' => $validated['year'] ?? null,
            'month' => $validated['month'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        $message = "Saved {$saved} mark(s)" . ($cleared ? ", cleared {$cleared}" : '') . ($skipped ? ", skipped {$skipped} invalid" : '') . '.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('admin.attendance.index', $params)
            ->with('success', $message);
    }

    public function updateCell(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'date' => 'required|date',
            'status' => 'nullable|in:present,absent,half_day,leave',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        $companyId = CompanyContext::getActiveCompanyId();
        $worker = Worker::where('company_id', $companyId)->findOrFail($validated['worker_id']);
        $date = Carbon::parse($validated['date'])->toDateString();

        if (empty($validated['status'])) {
            WorkerAttendance::where('worker_id', $worker->id)->whereDate('date', $date)->delete();

            return response()->json([
                'success' => true,
                'status' => null,
                'message' => 'Cleared',
            ]);
        }

        $error = $this->attendanceDateError($worker, $date);
        if ($error) {
            return response()->json([
                'success' => false,
                'message' => $error,
            ], 422);
        }

        WorkerAttendance::updateOrCreate(
            ['worker_id' => $worker->id, 'date' => $date],
            [
                'company_id' => $companyId,
                'project_id' => $validated['project_id'] ?? $worker->project_id,
                'status' => $validated['status'],
                'marked_by' => auth()->id(),
            ]
        );

        return response()->json([
            'success' => true,
            'status' => $validated['status'],
            'message' => 'Updated',
        ]);
    }

    public function markColumn(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:present,absent,half_day,leave',
            'project_id' => 'nullable|exists:projects,id',
            'calendar' => 'nullable|in:ad,bs',
            'year' => 'nullable|integer',
            'month' => 'nullable|integer',
        ]);

        $companyId = CompanyContext::getActiveCompanyId();
        $date = Carbon::parse($validated['date'])->toDateString();

        $params = array_filter([
            'calendar' => $validated['calendar'] ?? 'ad',
            'year' => $validated['year'] ?? null,
            'month' => $validated['month'] ?? null,
            'project_id' => $validated['project_id'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');

        if (Carbon::parse($date)->startOfDay()->gt(Carbon::today())) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot mark attendance for a future date.',
                ], 422);
            }

            return redirect()
                ->route('admin.attendance.index', $params)
                ->with('error', 'Cannot mark attendance for a future date.');
        }

        $workersQuery = Worker::where('company_id', $companyId)->where('is_active', true);
        if (! empty($validated['project_id'])) {
            $workersQuery->where(function ($q) use ($validated) {
                $q->where('project_id', $validated['project_id'])->orWhereNull('project_id');
            });
        }

        $marked = 0;
        foreach ($workersQuery->get() as $worker) {
            if ($this->attendanceDateError($worker, $date)) {
                continue;
            }

            WorkerAttendance::updateOrCreate(
                ['worker_id' => $worker->id, 'date' => $date],
                [
                    'company_id' => $companyId,
                    'project_id' => $validated['project_id'] ?? $worker->project_id,
                    'status' => $validated['status'],
                    'marked_by' => auth()->id(),
                ]
            );
            $marked++;
        }

        $message = "Marked {$marked} worker(s) as " . str_replace('_', ' ', $validated['status']) . '.';

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'marked' => $marked,
            ]);
        }

        return redirect()
            ->route('admin.attendance.index', $params)
            ->with('success', $message);
    }

    public function history(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $workerId = $request->get('worker_id');
        $projectId = $request->get('project_id');

        $query = WorkerAttendance::with(['worker', 'project'])
            ->where('company_id', $companyId)
            ->whereBetween('date', [$startDate, $endDate]);

        if ($workerId) {
            $query->where('worker_id', $workerId);
        }
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $records = $query->orderByDesc('date')->paginate(30)->withQueryString();
        $workers = Worker::where('company_id', $companyId)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();

        return view('admin.attendance.history', compact(
            'records',
            'workers',
            'projects',
            'startDate',
            'endDate',
            'workerId',
            'projectId'
        ));
    }
}
