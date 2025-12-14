<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Exports\ProjectMaterialsExport;
use App\Models\ConstructionMaterial;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Category;
use App\Models\Staff;
use App\Models\MaterialName;
use App\Models\Project;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Support\CompanyContext;
use App\Support\ProjectContext;

class ReportController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Show reports index page.
     */
    public function index()
    {
        return view('admin.reports.index');
    }

    /**
     * Financial Summary Report.
     */
    public function financialSummary(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $projectId = $request->get('project_id');
        $companyId = CompanyContext::getActiveCompanyId();

        $incomeQuery = Income::where('incomes.company_id', $companyId)
            ->whereBetween('incomes.date', [$startDate, $endDate]);
        $expenseQuery = Expense::where('expenses.company_id', $companyId)
            ->whereBetween('expenses.date', [$startDate, $endDate]);
        
        if ($projectId) {
            $incomeQuery->where('project_id', $projectId);
            $expenseQuery->where('project_id', $projectId);
        }
        
        $totalIncome = $incomeQuery->sum('amount');
        $totalExpenses = $expenseQuery->sum('amount');
        $netBalance = $totalIncome - $totalExpenses;

        // Income by category
        $incomeByCategoryQuery = Income::where('incomes.company_id', $companyId)->whereBetween('incomes.date', [$startDate, $endDate]);
        if ($projectId) {
            $incomeByCategoryQuery->where('project_id', $projectId);
        }
        $incomeByCategory = $incomeByCategoryQuery->join('categories', 'incomes.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(incomes.amount) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->get();

        // Expenses by category
        $expensesByCategoryQuery = Expense::where('expenses.company_id', $companyId)->whereBetween('expenses.date', [$startDate, $endDate]);
        if ($projectId) {
            $expensesByCategoryQuery->where('project_id', $projectId);
        }
        $expensesByCategory = $expensesByCategoryQuery->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->get();

        // Expenses by type
        $expensesByTypeQuery = Expense::where('expenses.company_id', $companyId)->whereBetween('expenses.date', [$startDate, $endDate]);
        if ($projectId) {
            $expensesByTypeQuery->where('project_id', $projectId);
        }
        $expensesByType = $expensesByTypeQuery->select('expense_type', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_type')
            ->get();

        // Monthly trend (last 6 months)
        $monthlyTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $monthStart = $month . '-01';
            $monthEnd = date('Y-m-t', strtotime($monthStart));
            
            $monthIncomeQuery = Income::where('incomes.company_id', $companyId)->whereBetween('incomes.date', [$monthStart, $monthEnd]);
            $monthExpenseQuery = Expense::where('expenses.company_id', $companyId)->whereBetween('expenses.date', [$monthStart, $monthEnd]);
            
            if ($projectId) {
                $monthIncomeQuery->where('project_id', $projectId);
                $monthExpenseQuery->where('project_id', $projectId);
            }
            
            $monthlyTrend[] = [
                'month' => date('M Y', strtotime($monthStart)),
                'income' => $monthIncomeQuery->sum('amount'),
                'expenses' => $monthExpenseQuery->sum('amount'),
            ];
        }
        
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();

        return view('admin.reports.financial-summary', compact(
            'startDate',
            'endDate',
            'projectId',
            'projects',
            'totalIncome',
            'totalExpenses',
            'netBalance',
            'incomeByCategory',
            'expensesByCategory',
            'expensesByType',
            'monthlyTrend'
        ));
    }

    /**
     * Income Report.
     */
    public function incomeReport(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $categoryId = $request->get('category_id');
        $projectId = $request->get('project_id');
        $companyId = CompanyContext::getActiveCompanyId();

        $query = Income::with(['category', 'subcategory', 'project'])
            ->where('incomes.company_id', $companyId)
            ->whereBetween('incomes.date', [$startDate, $endDate]);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $incomes = $query->orderBy('date', 'desc')->get();
        $totalIncome = $incomes->sum('amount');

        // Income by category
        $incomeByCategoryQuery = Income::where('incomes.company_id', $companyId)->whereBetween('incomes.date', [$startDate, $endDate]);
        if ($projectId) {
            $incomeByCategoryQuery->where('project_id', $projectId);
        }
        $incomeByCategory = $incomeByCategoryQuery->join('categories', 'incomes.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(incomes.amount) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->get();

        // Income by source
        $incomeBySourceQuery = Income::where('incomes.company_id', $companyId)->whereBetween('incomes.date', [$startDate, $endDate]);
        if ($projectId) {
            $incomeBySourceQuery->where('project_id', $projectId);
        }
        $incomeBySource = $incomeBySourceQuery->select('source', DB::raw('SUM(amount) as total'))
            ->groupBy('source')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $categories = Category::where('type', 'income')->where('is_active', true)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();

        return view('admin.reports.income', compact(
            'startDate',
            'endDate',
            'categoryId',
            'projectId',
            'projects',
            'incomes',
            'totalIncome',
            'incomeByCategory',
            'incomeBySource',
            'categories'
        ));
    }

    /**
     * Expense Report.
     */
    public function expenseReport(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $categoryId = $request->get('category_id');
        $expenseType = $request->get('expense_type');
        $projectId = $request->get('project_id');
        $companyId = CompanyContext::getActiveCompanyId();

        $query = Expense::with(['category', 'subcategory', 'staff', 'project'])
            ->where('expenses.company_id', $companyId)
            ->whereBetween('expenses.date', [$startDate, $endDate]);

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($expenseType) {
            $query->where('expense_type', $expenseType);
        }
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $expenses = $query->orderBy('date', 'desc')->get();
        $totalExpenses = $expenses->sum('amount');

        // Expenses by category
        $expensesByCategoryQuery = Expense::where('expenses.company_id', $companyId)->whereBetween('expenses.date', [$startDate, $endDate]);
        if ($projectId) {
            $expensesByCategoryQuery->where('project_id', $projectId);
        }
        $expensesByCategory = $expensesByCategoryQuery->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->get();

        // Expenses by type
        $expensesByTypeQuery = Expense::where('expenses.company_id', $companyId)->whereBetween('expenses.date', [$startDate, $endDate]);
        if ($projectId) {
            $expensesByTypeQuery->where('project_id', $projectId);
        }
        $expensesByType = $expensesByTypeQuery->select('expense_type', DB::raw('SUM(amount) as total'))
            ->groupBy('expense_type')
            ->get();

        $categories = Category::where('type', 'expense')->where('is_active', true)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();

        return view('admin.reports.expense', compact(
            'startDate',
            'endDate',
            'categoryId',
            'expenseType',
            'projectId',
            'projects',
            'expenses',
            'totalExpenses',
            'expensesByCategory',
            'expensesByType',
            'categories'
        ));
    }

    /**
     * Project Material Consumption Report.
     */
    public function projectMaterialsReport(Request $request)
    {
        $filters = $this->materialReportFilters($request);
        $baseQuery = $this->materialReportQuery($filters);

        $overall = [
            'deliveries' => (clone $baseQuery)->count(),
            'total_quantity' => (clone $baseQuery)->sum('quantity_received'),
            'total_cost' => (clone $baseQuery)->sum('total_cost'),
            'projects' => (clone $baseQuery)->distinct('project_name')->count('project_name'),
            'suppliers' => (clone $baseQuery)->distinct('supplier_name')->count('supplier_name'),
        ];

        $projectSummary = (clone $baseQuery)
            ->select(
                'project_name',
                DB::raw('COUNT(*) as deliveries'),
                DB::raw('SUM(quantity_received) as total_quantity'),
                DB::raw('SUM(total_cost) as total_cost'),
                DB::raw('SUM(quantity_used) as total_used'),
                DB::raw('SUM(quantity_remaining) as total_remaining'),
                DB::raw('COUNT(DISTINCT supplier_name) as supplier_count')
            )
            ->groupBy('project_name')
            ->orderByDesc('total_cost')
            ->get();

        $topMaterials = (clone $baseQuery)
            ->select('material_name', DB::raw('SUM(quantity_received) as total_quantity'), DB::raw('SUM(total_cost) as total_cost'))
            ->groupBy('material_name')
            ->orderByDesc('total_cost')
            ->limit(5)
            ->get();

        $topSuppliers = (clone $baseQuery)
            ->whereNotNull('supplier_name')
            ->select('supplier_name', DB::raw('SUM(total_cost) as total_cost'), DB::raw('SUM(quantity_received) as total_quantity'))
            ->groupBy('supplier_name')
            ->orderByDesc('total_cost')
            ->limit(5)
            ->get();

        $recentDeliveries = (clone $baseQuery)
            ->orderByDesc('delivery_date')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $materialOptions = MaterialName::orderBy('name')->get();
        $projectOptions = Project::orderBy('name')->get();
        $supplierOptions = Supplier::where('is_active', true)->orderBy('name')->get();

        $startDate = $filters['start_date'];
        $endDate = $filters['end_date'];
        $projectName = $filters['project_name'];
        $materialName = $filters['material_name'];
        $supplierName = $filters['supplier_name'];

        return view('admin.reports.project-materials', compact(
            'startDate',
            'endDate',
            'projectName',
            'materialName',
            'supplierName',
            'overall',
            'projectSummary',
            'topMaterials',
            'topSuppliers',
            'recentDeliveries',
            'materialOptions',
            'projectOptions',
            'supplierOptions'
        ));
    }

    public function projectMaterialsExport(Request $request)
    {
        $filters = $this->materialReportFilters($request);
        $query = $this->materialReportQuery($filters);

        $filename = 'project-materials-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new ProjectMaterialsExport($query), $filename);
    }

    protected function materialReportFilters(Request $request): array
    {
        return [
            'start_date' => $request->get('start_date', date('Y-m-01')),
            'end_date' => $request->get('end_date', date('Y-m-d')),
            'project_name' => $request->get('project_name'),
            'material_name' => $request->get('material_name'),
            'supplier_name' => $request->get('supplier_name'),
        ];
    }

    protected function materialReportQuery(array $filters)
    {
        $query = ConstructionMaterial::query();

        if (!empty($filters['start_date'])) {
            $query->whereDate('delivery_date', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('delivery_date', '<=', $filters['end_date']);
        }

        if (!empty($filters['project_name'])) {
            $query->where('project_name', $filters['project_name']);
        }

        if (!empty($filters['material_name'])) {
            $query->where('material_name', $filters['material_name']);
        }

        if (!empty($filters['supplier_name'])) {
            $query->where('supplier_name', $filters['supplier_name']);
        }

        return $query;
    }

    /**
     * Staff Payment Report.
     */
    public function staffPaymentReport(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $staffId = $request->get('staff_id');
        $projectId = $request->get('project_id');
        $companyId = CompanyContext::getActiveCompanyId();

        $query = Expense::with(['staff', 'project'])
            ->whereIn('expense_type', ['salary', 'advance'])
            ->where('expenses.company_id', $companyId)
            ->whereBetween('expenses.date', [$startDate, $endDate]);

        if ($staffId) {
            $query->where('staff_id', $staffId);
        }
        
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $payments = $query->orderBy('date', 'desc')->get();
        $totalPayments = $payments->sum('amount');

        // Payments by staff
        $paymentsByStaffQuery = Expense::whereIn('expense_type', ['salary', 'advance'])
            ->where('expenses.company_id', $companyId)->whereBetween('expenses.date', [$startDate, $endDate]);
        if ($projectId) {
            $paymentsByStaffQuery->where('project_id', $projectId);
        }
        $paymentsByStaff = $paymentsByStaffQuery->join('staff', 'expenses.staff_id', '=', 'staff.id')
            ->select('staff.name', 'expenses.expense_type', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('staff.id', 'staff.name', 'expenses.expense_type')
            ->get();

        // Total by staff
        $totalByStaffQuery = Expense::whereIn('expense_type', ['salary', 'advance'])
            ->where('expenses.company_id', $companyId)->whereBetween('expenses.date', [$startDate, $endDate]);
        if ($projectId) {
            $totalByStaffQuery->where('project_id', $projectId);
        }
        $totalByStaff = $totalByStaffQuery->join('staff', 'expenses.staff_id', '=', 'staff.id')
            ->select('staff.name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('staff.id', 'staff.name')
            ->orderByDesc('total')
            ->get();

        $staff = Staff::where('is_active', true)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();

        return view('admin.reports.staff-payment', compact(
            'startDate',
            'endDate',
            'staffId',
            'projectId',
            'projects',
            'payments',
            'totalPayments',
            'paymentsByStaff',
            'totalByStaff',
            'staff'
        ));
    }

    /**
     * Balance Sheet Report (Debit & Credit).
     */
    public function balanceSheet(Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $projectId = $request->get('project_id');
        $companyId = CompanyContext::getActiveCompanyId();

        // Total Income (Credit)
        $incomeQuery = Income::where('incomes.company_id', $companyId)->whereBetween('incomes.date', [$startDate, $endDate]);
        if ($projectId) {
            $incomeQuery->where('project_id', $projectId);
        }
        $totalIncome = $incomeQuery->sum('amount');
        
        // Total Expenses (Debit)
        $expenseQuery = Expense::where('expenses.company_id', $companyId)->whereBetween('expenses.date', [$startDate, $endDate]);
        if ($projectId) {
            $expenseQuery->where('project_id', $projectId);
        }
        $totalExpenses = $expenseQuery->sum('amount');
        
        // Net Profit/Loss
        $netProfit = $totalIncome - $totalExpenses;

        // Balance Sheet Calculation
        $totalDebits = $totalExpenses;
        $totalCredits = $totalIncome;
        $balance = abs($totalCredits - $totalDebits);

        // Debit Details by Category
        $debitByCategoryQuery = Expense::where('expenses.company_id', $companyId)->whereBetween('expenses.date', [$startDate, $endDate]);
        if ($projectId) {
            $debitByCategoryQuery->where('project_id', $projectId);
        }
        $debitByCategory = $debitByCategoryQuery->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->get();

        // Credit Details by Category
        $creditByCategoryQuery = Income::where('incomes.company_id', $companyId)->whereBetween('incomes.date', [$startDate, $endDate]);
        if ($projectId) {
            $creditByCategoryQuery->where('project_id', $projectId);
        }
        $creditByCategory = $creditByCategoryQuery->join('categories', 'incomes.category_id', '=', 'categories.id')
            ->select('categories.name', DB::raw('SUM(incomes.amount) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total')
            ->get();

        // Detailed Debit Records
        $debitRecordsQuery = Expense::with(['category', 'subcategory', 'project'])
            ->where('expenses.company_id', $companyId)->whereBetween('expenses.date', [$startDate, $endDate]);
        if ($projectId) {
            $debitRecordsQuery->where('project_id', $projectId);
        }
        $debitRecords = $debitRecordsQuery->orderBy('date', 'asc')->get();

        // Detailed Credit Records
        $creditRecordsQuery = Income::with(['category', 'subcategory', 'project'])
            ->where('incomes.company_id', $companyId)->whereBetween('incomes.date', [$startDate, $endDate]);
        if ($projectId) {
            $creditRecordsQuery->where('project_id', $projectId);
        }
        $creditRecords = $creditRecordsQuery->orderBy('date', 'asc')->get();
        
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();

        return view('admin.reports.balance-sheet', compact(
            'startDate',
            'endDate',
            'projectId',
            'projects',
            'totalIncome',
            'totalExpenses',
            'netProfit',
            'totalDebits',
            'totalCredits',
            'balance',
            'debitByCategory',
            'creditByCategory',
            'debitRecords',
            'creditRecords'
        ));
    }
}
