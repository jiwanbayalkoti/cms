<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Category;
use App\Models\Staff;
use Illuminate\Http\Request;
use App\Support\CompanyContext;

class DashboardController extends Controller
{
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
    public function index()
    {
        // Current month statistics
        $currentMonthStart = date('Y-m-01');
        $currentMonthEnd = date('Y-m-d');
        $companyId = CompanyContext::getActiveCompanyId();
        
        $totalIncome = Income::where('company_id', $companyId)
            ->whereBetween('date', [$currentMonthStart, $currentMonthEnd])->sum('amount');
        $totalExpenses = Expense::where('company_id', $companyId)
            ->whereBetween('date', [$currentMonthStart, $currentMonthEnd])->sum('amount');
        $balance = $totalIncome - $totalExpenses;
        
        // Total counts
        $totalStaff = Staff::where('company_id', $companyId)
            ->where('is_active', true)->count();
        
        // Recent transactions
        $recentIncomes = Income::with(['category'])
            ->where('company_id', $companyId)
            ->latest('date')->limit(5)->get();
        $recentExpenses = Expense::with(['category', 'staff'])
            ->where('company_id', $companyId)
            ->latest('date')->limit(5)->get();
        
        // Chart data - Last 12 months
        $monthlyData = $this->getMonthlyData($companyId, 12);
        
        // Category breakdown for current month
        $incomeByCategory = $this->getIncomeByCategory($companyId, $currentMonthStart, $currentMonthEnd);
        $expenseByCategory = $this->getExpenseByCategory($companyId, $currentMonthStart, $currentMonthEnd);
        
        return view('admin.dashboard', compact(
            'totalIncome',
            'totalExpenses',
            'balance',
            'totalStaff',
            'recentIncomes',
            'recentExpenses',
            'monthlyData',
            'incomeByCategory',
            'expenseByCategory'
        ));
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
            
            $income = Income::where('company_id', $companyId)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('amount');
            $incomeData[] = (float) $income;
            
            $expense = Expense::where('company_id', $companyId)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('amount');
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
        return Income::join('categories', 'incomes.category_id', '=', 'categories.id')
            ->where('incomes.company_id', $companyId)
            ->whereBetween('incomes.date', [$startDate, $endDate])
            ->selectRaw('categories.name as category, SUM(incomes.amount) as total')
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
        return Expense::join('categories', 'expenses.category_id', '=', 'categories.id')
            ->where('expenses.company_id', $companyId)
            ->whereBetween('expenses.date', [$startDate, $endDate])
            ->selectRaw('categories.name as category, SUM(expenses.amount) as total')
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
}
