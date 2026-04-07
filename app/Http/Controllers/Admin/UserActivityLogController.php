<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\UserActivityLogExport;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class UserActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = auth()->user();

        $query = $this->buildFilteredQuery($request, $currentUser);

        $logs = $query->paginate(30)->withQueryString();

        if ($request->get('filter_ajax') === '1' || $request->wantsJson()) {
            $listHtml = view('admin.user-activities.partials._list', compact('logs'))->render();

            return response()->json([
                'html' => $listHtml,
            ]);
        }

        $baseStatsQuery = UserActivityLog::query()->where('method', '!=', 'GET');
        if (!$currentUser->isSuperAdmin()) {
            $baseStatsQuery->where(function ($q) use ($currentUser) {
                $q->where('company_id', $currentUser->company_id)
                    ->orWhere('user_id', $currentUser->id);
            });
        }

        $stats = [
            'today' => (clone $baseStatsQuery)->whereDate('created_at', now()->toDateString())->count(),
            'week' => (clone $baseStatsQuery)->where('created_at', '>=', now()->subDays(7))->count(),
            'month' => (clone $baseStatsQuery)->where('created_at', '>=', now()->subDays(30))->count(),
            'total' => (clone $baseStatsQuery)->count(),
        ];

        $userOptionsQuery = User::query()->orderBy('name');
        if (!$currentUser->isSuperAdmin()) {
            $userOptionsQuery->where('company_id', $currentUser->company_id);
        }
        $users = $userOptionsQuery->select('id', 'name', 'email')->get();

        return view('admin.user-activities.index', compact('logs', 'stats', 'users'));
    }

    public function exportExcel(Request $request)
    {
        $currentUser = auth()->user();
        $query = $this->buildFilteredQuery($request, $currentUser);

        $filename = 'user-activity-logs-' . now()->format('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new UserActivityLogExport($query), $filename);
    }

    protected function buildFilteredQuery(Request $request, $currentUser)
    {
        $query = UserActivityLog::with(['user:id,name,email', 'company:id,name'])
            ->where('method', '!=', 'GET')
            ->latest();

        if (!$currentUser->isSuperAdmin()) {
            $query->where(function ($q) use ($currentUser) {
                $q->where('company_id', $currentUser->company_id)
                    ->orWhere('user_id', $currentUser->id);
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->user_id);
        }

        if ($request->filled('action_filter')) {
            $actionFilter = (string) $request->input('action_filter');
            $query->where('action', 'like', '%' . $actionFilter . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('route_name', 'like', '%' . $search . '%')
                    ->orWhere('url', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', '%' . $search . '%')
                            ->orWhere('email', 'like', '%' . $search . '%');
                    });
            });
        }

        return $query;
    }
}

