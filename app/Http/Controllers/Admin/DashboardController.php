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
        $totalStaff = Staff::where('is_active', true)->count();
        
        // Recent transactions
        $recentIncomes = Income::with(['category'])
            ->where('company_id', $companyId)
            ->latest('date')->limit(5)->get();
        $recentExpenses = Expense::with(['category', 'staff'])
            ->where('company_id', $companyId)
            ->latest('date')->limit(5)->get();
        
        return view('admin.dashboard', compact(
            'totalIncome',
            'totalExpenses',
            'balance',
            'totalStaff',
            'recentIncomes',
            'recentExpenses'
        ));
    }
}
