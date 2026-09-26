<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Models\WorkerPayment;
use App\Models\Project;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Support\CompanyContext;
use Illuminate\Support\Facades\DB;

class WorkerPaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $query = WorkerPayment::with(['worker', 'project', 'expense'])
            ->where('company_id', $companyId);

        if ($request->filled('worker_id')) {
            $query->where('worker_id', $request->worker_id);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('wage_type')) {
            $query->where('wage_type', $request->wage_type);
        }

        $payments = $query->latest('payment_date')->paginate(15)->withQueryString();
        $workers = Worker::where('company_id', $companyId)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();

        $totalPaid = WorkerPayment::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->when($request->filled('worker_id'), fn ($q) => $q->where('worker_id', $request->worker_id))
            ->when($request->filled('project_id'), fn ($q) => $q->where('project_id', $request->project_id))
            ->sum('paid_amount');

        return view('admin.worker_payments.index', compact(
            'payments',
            'workers',
            'projects',
            'totalPaid'
        ));
    }

    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $workers = Worker::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Worker $w) => [
                'id' => $w->id,
                'name' => $w->name,
                'wage_type' => $w->wage_type,
                'daily_wage' => $w->daily_wage,
                'monthly_salary' => $w->monthly_salary,
                'project_id' => $w->project_id,
                'label' => $w->name . ' (' . ucfirst($w->wage_type) . ' — Rs. ' . number_format(
                    $w->wage_type === 'daily' ? (float) $w->daily_wage : (float) $w->monthly_salary,
                    2
                ) . ')',
            ]);
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get(['id', 'name']);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'workers' => $workers,
                'projects' => $projects,
            ]);
        }

        return redirect()->route('admin.worker-payments.index');
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'bonus' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'filter_period' => 'nullable|boolean',
        ]);

        $companyId = CompanyContext::getActiveCompanyId();
        $worker = Worker::where('company_id', $companyId)->findOrFail($validated['worker_id']);

        $calc = WorkerPayment::calculateForWorker(
            $worker,
            $validated['period_start'],
            $validated['period_end'],
            (float) ($validated['bonus'] ?? 0),
            (float) ($validated['deduction'] ?? 0)
        );

        $start = $validated['period_start'];
        $end = $validated['period_end'];
        $filterPeriod = $request->boolean('filter_period');

        // Already paid for this period = payments whose period overlaps selection
        $alreadyPaid = (float) WorkerPayment::where('company_id', $companyId)
            ->where('worker_id', $worker->id)
            ->where('status', '!=', 'cancelled')
            ->where('period_start', '<=', $end)
            ->where('period_end', '>=', $start)
            ->sum('paid_amount');

        $dueAmount = round((float) $calc['net_amount'] - $alreadyPaid, 2);

        // History list: all payments by default; filter by period when requested
        $historyQuery = WorkerPayment::where('company_id', $companyId)
            ->where('worker_id', $worker->id)
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('payment_date')
            ->orderByDesc('id');

        if ($filterPeriod) {
            $historyQuery
                ->where('period_start', '<=', $end)
                ->where('period_end', '>=', $start);
        }

        $previousPayments = $historyQuery->limit(50)->get()->map(fn (WorkerPayment $p) => [
            'id' => $p->id,
            'payment_date' => $p->payment_date?->format('Y-m-d'),
            'period_start' => $p->period_start?->format('Y-m-d'),
            'period_end' => $p->period_end?->format('Y-m-d'),
            'period_label' => $p->period_start?->format('M d') . ' – ' . $p->period_end?->format('M d, Y'),
            'wage_type' => $p->wage_type,
            'net_amount' => (float) $p->net_amount,
            'paid_amount' => (float) $p->paid_amount,
            'status' => $p->status,
        ]);

        return response()->json([
            'success' => true,
            'worker' => [
                'id' => $worker->id,
                'name' => $worker->name,
                'wage_type' => $worker->wage_type,
                'daily_wage' => $worker->daily_wage,
                'monthly_salary' => $worker->monthly_salary,
                'project_id' => $worker->project_id,
            ],
            'calculation' => $calc,
            'already_paid' => round($alreadyPaid, 2),
            'due_amount' => $dueAmount,
            'previous_payments' => $previousPayments,
            'list_scope' => $filterPeriod ? 'period' : 'all',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:workers,id',
            'project_id' => 'nullable|exists:projects,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'bonus' => 'nullable|numeric|min:0',
            'deduction' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'create_expense' => 'nullable|boolean',
        ]);

        $companyId = CompanyContext::getActiveCompanyId();
        $worker = Worker::where('company_id', $companyId)->findOrFail($validated['worker_id']);

        $calc = WorkerPayment::calculateForWorker(
            $worker,
            $validated['period_start'],
            $validated['period_end'],
            (float) ($validated['bonus'] ?? 0),
            (float) ($validated['deduction'] ?? 0)
        );

        $net = $calc['net_amount'];

        $alreadyPaid = (float) WorkerPayment::where('company_id', $companyId)
            ->where('worker_id', $worker->id)
            ->where('status', '!=', 'cancelled')
            ->where('period_start', '<=', $validated['period_end'])
            ->where('period_end', '>=', $validated['period_start'])
            ->sum('paid_amount');

        $due = round($net - $alreadyPaid, 2);

        $paid = array_key_exists('paid_amount', $validated) && $validated['paid_amount'] !== null
            ? (float) $validated['paid_amount']
            : max(0, $due);

        if ($paid <= 0) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => $due <= 0
                        ? ($due < 0
                            ? 'This period is already overpaid. Balance is in minus; no further payment needed.'
                            : 'Nothing due for this period. Previous payments already cover the net amount.')
                        : 'Paid amount must be greater than zero. Check attendance for this period.',
                    'errors' => [
                        'paid_amount' => [
                            $due <= 0
                                ? ($due < 0
                                    ? 'This period is already overpaid. Balance is in minus; no further payment needed.'
                                    : 'Nothing due for this period. Previous payments already cover the net amount.')
                                : 'Paid amount must be greater than zero. Check attendance for this period.',
                        ],
                    ],
                ], 422);
            }

            return back()->withInput()->withErrors([
                'paid_amount' => $due <= 0
                    ? ($due < 0
                        ? 'This period is already overpaid. Balance is in minus; no further payment needed.'
                        : 'Nothing due for this period. Previous payments already cover the net amount.')
                    : 'Paid amount must be greater than zero. Check attendance for this period.',
            ]);
        }

        $status = ($due > 0 && $paid < $due) ? 'partial' : 'paid';

        $payment = DB::transaction(function () use ($validated, $worker, $companyId, $calc, $net, $paid, $status, $request) {
            $payment = WorkerPayment::create([
                'company_id' => $companyId,
                'worker_id' => $worker->id,
                'project_id' => $validated['project_id'] ?? $worker->project_id,
                'wage_type' => $calc['wage_type'],
                'period_start' => $validated['period_start'],
                'period_end' => $validated['period_end'],
                'present_days' => $calc['present_days'],
                'half_days' => $calc['half_days'],
                'absent_days' => $calc['absent_days'],
                'leave_days' => $calc['leave_days'],
                'total_days' => $calc['total_days'],
                'overtime_hours' => $calc['overtime_hours'],
                'daily_wage' => $calc['daily_wage'],
                'monthly_salary' => $calc['monthly_salary'],
                'calculated_amount' => $calc['calculated_amount'],
                'overtime_amount' => $calc['overtime_amount'],
                'bonus' => (float) ($validated['bonus'] ?? 0),
                'deduction' => (float) ($validated['deduction'] ?? 0),
                'net_amount' => $net,
                'paid_amount' => $paid,
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'] ?? null,
                'status' => $status,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            if ($request->boolean('create_expense')) {
                $this->createExpenseFromPayment($payment);
            }

            return $payment;
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Worker payment recorded successfully.',
                'payment_id' => $payment->id,
            ]);
        }

        return redirect()->route('admin.worker-payments.index')
            ->with('success', 'Worker payment recorded and linked to expenses.');
    }

    public function show(WorkerPayment $workerPayment)
    {
        $this->assertCompany($workerPayment);
        $workerPayment->load(['worker', 'project', 'expense', 'creator']);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'payment' => [
                    'id' => $workerPayment->id,
                    'worker' => $workerPayment->worker?->name,
                    'project' => $workerPayment->project?->name,
                    'wage_type' => ucfirst($workerPayment->wage_type),
                    'period' => $workerPayment->period_start->format('M d, Y') . ' – ' . $workerPayment->period_end->format('M d, Y'),
                    'present_days' => $workerPayment->present_days,
                    'half_days' => $workerPayment->half_days,
                    'absent_days' => $workerPayment->absent_days,
                    'leave_days' => $workerPayment->leave_days,
                    'overtime_hours' => $workerPayment->overtime_hours,
                    'calculated_amount' => (float) $workerPayment->calculated_amount,
                    'overtime_amount' => (float) $workerPayment->overtime_amount,
                    'bonus' => (float) $workerPayment->bonus,
                    'deduction' => (float) $workerPayment->deduction,
                    'net_amount' => (float) $workerPayment->net_amount,
                    'paid_amount' => (float) $workerPayment->paid_amount,
                    'payment_date' => $workerPayment->payment_date?->format('M d, Y'),
                    'payment_method' => $workerPayment->payment_method,
                    'status' => ucfirst($workerPayment->status),
                    'notes' => $workerPayment->notes,
                    'expense_id' => $workerPayment->expense_id,
                    'expense_url' => $workerPayment->expense_id
                        ? route('admin.expenses.show', $workerPayment->expense_id)
                        : null,
                ],
            ]);
        }

        return redirect()->route('admin.worker-payments.index', ['view' => $workerPayment->id]);
    }

    public function destroy(Request $request, WorkerPayment $workerPayment)
    {
        $this->assertCompany($workerPayment);

        DB::transaction(function () use ($workerPayment) {
            if ($workerPayment->expense_id) {
                Expense::where('id', $workerPayment->expense_id)->delete();
            }
            $workerPayment->delete();
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Payment deleted.']);
        }

        return redirect()->route('admin.worker-payments.index')
            ->with('success', 'Worker payment deleted.');
    }

    protected function createExpenseFromPayment(WorkerPayment $payment): void
    {
        $payment->load('worker');
        $workerName = $payment->worker->name ?? 'Worker';
        $period = $payment->period_start->format('M d') . ' – ' . $payment->period_end->format('M d, Y');
        $wageLabel = $payment->wage_type === 'monthly' ? 'Monthly' : 'Daily';

        $expenseType = ExpenseType::firstOrCreate(
            ['name' => 'Worker Wages'],
            ['name' => 'Worker Wages']
        );
        $category = Category::firstOrCreateDefaultExpense();

        $expense = Expense::create([
            'company_id' => $payment->company_id,
            'project_id' => $payment->project_id,
            'worker_id' => $payment->worker_id,
            'worker_payment_id' => $payment->id,
            'category_id' => $category->id,
            'expense_type_id' => $expenseType->id,
            'item_name' => "Worker Payment - {$workerName}",
            'description' => "{$wageLabel} wages for {$workerName} ({$period})"
                . " | Present: {$payment->present_days}, Half: {$payment->half_days}"
                . ($payment->notes ? " | {$payment->notes}" : ''),
            'amount' => $payment->paid_amount,
            'date' => $payment->payment_date,
            'payment_method' => $payment->payment_method,
            'notes' => $payment->notes,
            'created_by' => auth()->id(),
        ]);

        $payment->expense_id = $expense->id;
        $payment->save();
    }

    protected function assertCompany(WorkerPayment $payment): void
    {
        $companyId = CompanyContext::getActiveCompanyId();
        if ($companyId && (int) $companyId !== 1 && (int) $payment->company_id !== (int) $companyId) {
            abort(403);
        }
    }
}
