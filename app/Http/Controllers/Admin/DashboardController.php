<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\HasProjectAccess;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Category;
use App\Models\Staff;
use App\Models\Company;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Support\CompanyContext;
use Carbon\Carbon;

class DashboardController extends Controller
{
    use HasProjectAccess;
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Show the admin dashboard.
     */
    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        // Get period filter (default: all time)
        $period = $request->get('period', 'all_time');
        $dateRange = $this->getDateRange($period);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        
        // Statistics for selected period - filter by accessible projects
        // Remove global scope to show all accessible projects, not just selected project
        $incomeQuery = Income::withoutGlobalScope('project')
            ->where('company_id', $companyId)
            ->whereBetween('date', [$startDate, $endDate]);
        $this->filterByAccessibleProjects($incomeQuery, 'project_id');
        $totalIncome = $incomeQuery->sum('amount');
        
        $expenseQuery = Expense::withoutGlobalScope('project')
            ->where('company_id', $companyId)
            ->whereBetween('date', [$startDate, $endDate]);
        $this->filterByAccessibleProjects($expenseQuery, 'project_id');
        $totalExpenses = $expenseQuery->sum('amount');
        $balance = $totalIncome - $totalExpenses;
        
        // Total counts (not affected by period)
        $totalStaff = Staff::where('company_id', $companyId)
            ->where('is_active', true)->count();
        
        // Count only accessible projects
        $projectsQuery = Project::where('company_id', $companyId);
        $this->filterByAccessibleProjects($projectsQuery, 'id');
        $totalProjects = $projectsQuery->count();
        
        // For super admin only: get total projects across all companies, total companies, and email statistics
        $totalAllProjects = null;
        $totalCompanies = null;
        $emailApiUsageCount = null;
        $emailApiUsageLimit = null;
        $emailNotificationCount = null;
        
        // Only super admin can see email check and email send counts
        if (auth()->user()->isSuperAdmin()) {
            $totalAllProjects = Project::count();
            $totalCompanies = Company::count();
            // Get email validation API usage count (only for super admin)
            $emailApiUsageCount = \App\Rules\ValidEmailDomain::getApiUsageCount();
            $emailApiUsageLimit = \App\Rules\ValidEmailDomain::getApiUsageLimit();
            // Get email notification count (user account created + password changed) - only for super admin
            $emailNotificationCount = \App\Mail\UserAccountCreated::getEmailCount() + \App\Mail\PasswordChanged::getEmailCount();
        }
        
        // Recent transactions (last 5 regardless of period) - filter by accessible projects
        // Remove global scope to show all accessible projects, not just selected project
        $recentIncomesQuery = Income::withoutGlobalScope('project')
            ->with(['category'])
            ->where('company_id', $companyId)
            ->latest('date');
        $this->filterByAccessibleProjects($recentIncomesQuery, 'project_id');
        $recentIncomes = $recentIncomesQuery->limit(5)->get();
        
        $recentExpensesQuery = Expense::withoutGlobalScope('project')
            ->with(['category', 'staff'])
            ->where('company_id', $companyId)
            ->latest('date');
        $this->filterByAccessibleProjects($recentExpensesQuery, 'project_id');
        $recentExpenses = $recentExpensesQuery->limit(5)->get();
        
        // Chart data - adjust based on period
        $chartMonths = $this->getChartMonthsForPeriod($period);
        $monthlyData = $this->getMonthlyData($companyId, $chartMonths);
        
        // Category breakdown for selected period
        $incomeByCategory = $this->getIncomeByCategory($companyId, $startDate, $endDate);
        $expenseByCategory = $this->getExpenseByCategory($companyId, $startDate, $endDate);
        
        // Format dates for display
        $startDateFormatted = Carbon::parse($startDate)->format('M d');
        $endDateFormatted = Carbon::parse($endDate)->format('M d, Y');
        
        return view('admin.dashboard', compact(
            'totalIncome',
            'totalExpenses',
            'balance',
            'totalStaff',
            'totalProjects',
            'totalAllProjects',
            'totalCompanies',
            'emailApiUsageCount',
            'emailApiUsageLimit',
            'emailNotificationCount',
            'recentIncomes',
            'recentExpenses',
            'monthlyData',
            'incomeByCategory',
            'expenseByCategory',
            'period',
            'startDate',
            'endDate',
            'startDateFormatted',
            'endDateFormatted'
        ));
    }
    
    /**
     * Get date range based on period filter.
     */
    private function getDateRange($period)
    {
        $endDate = now()->format('Y-m-d');
        
        switch ($period) {
            case '1_month':
                $startDate = now()->startOfMonth()->format('Y-m-d');
                break;
            case '3_months':
                $startDate = now()->subMonths(2)->startOfMonth()->format('Y-m-d');
                break;
            case '6_months':
                $startDate = now()->subMonths(5)->startOfMonth()->format('Y-m-d');
                break;
            case '1_year':
                $startDate = now()->subYear()->startOfMonth()->format('Y-m-d');
                break;
            case 'all_time':
                // Get earliest record date or default to 1 year ago
                $startDate = now()->subYears(2)->startOfMonth()->format('Y-m-d');
                break;
            default:
                $startDate = now()->startOfMonth()->format('Y-m-d');
        }
        
        return [
            'start' => $startDate,
            'end' => $endDate,
        ];
    }
    
    /**
     * Get number of months for chart based on period.
     */
    private function getChartMonthsForPeriod($period)
    {
        switch ($period) {
            case '1_month':
                return 1;
            case '3_months':
                return 3;
            case '6_months':
                return 6;
            case '1_year':
                return 12;
            case 'all_time':
                return 24; // Show last 2 years
            default:
                return 12;
        }
    }
    
    /**
     * Get monthly income and expense data for charts.
     */
    private function getMonthlyData($companyId, $months = 12)
    {
        $data = [];
        $labels = [];
        $incomeData = [];
        $expenseData = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth()->format('Y-m-d');
            $monthEnd = $date->copy()->endOfMonth()->format('Y-m-d');
            
            $labels[] = $date->format('M Y');
            
            // Remove global scope to show all accessible projects, not just selected project
            $incomeQuery = Income::withoutGlobalScope('project')
                ->where('company_id', $companyId)
                ->whereBetween('date', [$monthStart, $monthEnd]);
            $this->filterByAccessibleProjects($incomeQuery, 'project_id');
            $income = $incomeQuery->sum('amount');
            $incomeData[] = (float) $income;
            
            $expenseQuery = Expense::withoutGlobalScope('project')
                ->where('company_id', $companyId)
                ->whereBetween('date', [$monthStart, $monthEnd]);
            $this->filterByAccessibleProjects($expenseQuery, 'project_id');
            $expense = $expenseQuery->sum('amount');
            $expenseData[] = (float) $expense;
        }
        
        return [
            'labels' => $labels,
            'income' => $incomeData,
            'expenses' => $expenseData,
        ];
    }
    
    /**
     * Get income breakdown by category.
     */
    private function getIncomeByCategory($companyId, $startDate, $endDate)
    {
        // Remove global scope to show all accessible projects, not just selected project
        $query = Income::withoutGlobalScope('project')
            ->join('categories', 'incomes.category_id', '=', 'categories.id')
            ->where('incomes.company_id', $companyId)
            ->whereBetween('incomes.date', [$startDate, $endDate]);
        
        // Filter by accessible projects
        $this->filterByAccessibleProjects($query, 'incomes.project_id');
        
        return $query->selectRaw('categories.name as category, SUM(incomes.amount) as total')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->category,
                    'value' => (float) $item->total,
                ];
            })
            ->toArray();
    }
    
    /**
     * Get expense breakdown by category.
     */
    private function getExpenseByCategory($companyId, $startDate, $endDate)
    {
        // Remove global scope to show all accessible projects, not just selected project
        $query = Expense::withoutGlobalScope('project')
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->where('expenses.company_id', $companyId)
            ->whereBetween('expenses.date', [$startDate, $endDate]);
        
        // Filter by accessible projects
        $this->filterByAccessibleProjects($query, 'expenses.project_id');
        
        return $query->selectRaw('categories.name as category, SUM(expenses.amount) as total')
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->category,
                    'value' => (float) $item->total,
                ];
            })
            ->toArray();
    }

    /**
     * Show the super admin dashboard with company project counts.
     */
    public function superAdminDashboard()
    {
        // Get all companies with their project counts
        $companies = Company::withCount('projects')
            ->orderBy('name')
            ->get();

        return view('admin.super_admin.dashboard', compact('companies'));
    }
}
