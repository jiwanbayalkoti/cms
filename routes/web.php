<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ConstructionMaterialController;
use App\Http\Controllers\Admin\MaterialCategoryController;
use App\Http\Controllers\Admin\MaterialUnitController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\WorkTypeController;
use App\Http\Controllers\Admin\IncomeController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\ExpenseTypeController;
use App\Http\Controllers\Admin\PaymentTypeController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BillModuleController;
use App\Http\Controllers\Admin\MaterialCalculatorController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        // Redirect to dashboard if user is admin or super_admin
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        // Regular users might have a different dashboard or be redirected to login
        return redirect()->route('admin.login')->with('error', 'You do not have access to the admin panel.');
    }
    return redirect()->route('admin.login');
});

// Admin Authentication Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Login Routes (accessible only to guests)
    Route::middleware(['guest', 'throttle:5,1'])->group(function () {
        Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [LoginController::class, 'login']);
    });
    
    // Logout Route (accessible only to authenticated admins)
    Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Protected Admin Routes
    Route::middleware(['admin'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Super Admin Dashboard
        Route::middleware(['super_admin'])->group(function () {
            Route::get('/super-admin/dashboard', [DashboardController::class, 'superAdminDashboard'])->name('super-admin.dashboard');
        });
        
        // Categories CRUD
        Route::resource('categories', CategoryController::class);
        Route::post('categories/validate', [CategoryController::class, 'validateCategory'])->name('categories.validate');
        Route::get('categories/{category}/subcategories', [CategoryController::class, 'getSubcategories'])->name('categories.subcategories');
        
        // Subcategories CRUD
        Route::resource('subcategories', SubcategoryController::class);
        Route::post('subcategories/validate', [SubcategoryController::class, 'validateSubcategory'])->name('subcategories.validate');
        
        // Staff CRUD
        Route::resource('staff', StaffController::class);
        Route::post('staff/validate', [StaffController::class, 'validateStaff'])->name('staff.validate');
        Route::post('staff/{staff}/validate', [StaffController::class, 'validateStaff'])->name('staff.validate.edit');
        Route::get('staff/{staff}/details', [StaffController::class, 'getDetails'])->name('staff.details');
        
        // Positions CRUD
        Route::resource('positions', PositionController::class);
        
        // Projects CRUD
        Route::resource('projects', ProjectController::class);
        Route::get('projects/{project}/gallery', [ProjectController::class, 'gallery'])->name('projects.gallery');
        Route::post('projects/validate', [ProjectController::class, 'validateProjectForm'])->name('projects.validate');
        Route::post('projects/switch', [ProjectController::class, 'switch'])->name('projects.switch');
        
        // Construction Materials CRUD
        Route::resource('construction-materials', ConstructionMaterialController::class);
        Route::post('construction-materials/validate', [ConstructionMaterialController::class, 'validateMaterial'])->name('construction-materials.validate');
        Route::get('construction-materials/{construction_material}/clone', [ConstructionMaterialController::class, 'clone'])->name('construction-materials.clone');
        Route::resource('material-categories', MaterialCategoryController::class)->except(['show']);
        Route::resource('material-units', MaterialUnitController::class)->except(['show']);
        Route::resource('suppliers', SupplierController::class);
        Route::post('suppliers/validate', [SupplierController::class, 'validateSupplier'])->name('suppliers.validate');
        Route::post('suppliers/{supplier}/validate', [SupplierController::class, 'validateSupplier'])->name('suppliers.validate.edit');
        Route::resource('work-types', WorkTypeController::class)->except(['show']);
        Route::resource('material-names', \App\Http\Controllers\Admin\MaterialNameController::class);
        Route::resource('payment-modes', \App\Http\Controllers\Admin\PaymentModeController::class)->except(['show']);
        Route::resource('purchased-bies', \App\Http\Controllers\Admin\PurchasedByController::class)->except(['show']);
        
        // Income CRUD
        Route::resource('incomes', IncomeController::class);
        Route::post('incomes/validate', [IncomeController::class, 'validateIncome'])->name('incomes.validate');
        
        // Expenses CRUD
        Route::resource('expenses', ExpenseController::class);
        Route::post('expenses/validate', [ExpenseController::class, 'validateExpense'])->name('expenses.validate');
        Route::get('expenses/{expense}/clone', [ExpenseController::class, 'clone'])->name('expenses.clone');
        Route::resource('expense-types', ExpenseTypeController::class);
        
        // Bill Modules (Construction Final Bill / Estimate)
        Route::resource('bill-modules', BillModuleController::class);
        Route::resource('bill-categories', \App\Http\Controllers\Admin\BillCategoryController::class);
        Route::resource('bill-subcategories', \App\Http\Controllers\Admin\BillSubcategoryController::class);
        
        // Completed Works
        Route::resource('completed-works', \App\Http\Controllers\Admin\CompletedWorkController::class);
        Route::get('completed-works/generate/bill', [\App\Http\Controllers\Admin\CompletedWorkController::class, 'generateBillForm'])->name('completed-works.generate-bill');
        Route::post('completed-works/generate/bill', [\App\Http\Controllers\Admin\CompletedWorkController::class, 'generateBill'])->name('completed-works.generate-bill.store');
        Route::post('bill-modules/{bill_module}/submit', [BillModuleController::class, 'submit'])->name('bill-modules.submit');
        Route::post('bill-modules/{bill_module}/approve', [BillModuleController::class, 'approve'])->name('bill-modules.approve');
        Route::get('bill-modules/{bill_module}/export/excel', [BillModuleController::class, 'exportExcel'])->name('bill-modules.export.excel');
        Route::get('bill-modules/{bill_module}/export/pdf', [BillModuleController::class, 'exportPdf'])->name('bill-modules.export.pdf');
        Route::get('bill-modules/{bill_module}/report', [BillModuleController::class, 'report'])->name('bill-modules.report');
        Route::get('bill-modules/{bill_module}/items', [BillModuleController::class, 'getItems'])->name('bill-modules.items');

        // Material calculator
        Route::get('material-calculator', [MaterialCalculatorController::class, 'index'])->name('material-calculator.index');
        Route::get('material-calculator/my-history', [MaterialCalculatorController::class, 'getMyHistory'])->name('material-calculator.my-history');
        Route::post('material-calculator/export/excel', [MaterialCalculatorController::class, 'exportExcel'])->name('material-calculator.export.excel');
        Route::post('material-calculator/export/pdf', [MaterialCalculatorController::class, 'exportPdf'])->name('material-calculator.export.pdf');
        Route::post('material-calculator/save', [MaterialCalculatorController::class, 'save'])->name('material-calculator.save');
        
        // Accounting System
        Route::resource('chart-of-accounts', \App\Http\Controllers\Admin\ChartOfAccountController::class);
        Route::post('chart-of-accounts/seed-defaults', [\App\Http\Controllers\Admin\ChartOfAccountController::class, 'seedDefaults'])->name('chart-of-accounts.seed-defaults');
        
        Route::resource('journal-entries', \App\Http\Controllers\Admin\JournalEntryController::class);
        Route::post('journal-entries/{journal_entry}/post', [\App\Http\Controllers\Admin\JournalEntryController::class, 'post'])->name('journal-entries.post');
        Route::post('journal-entries/{journal_entry}/unpost', [\App\Http\Controllers\Admin\JournalEntryController::class, 'unpost'])->name('journal-entries.unpost');
        
        Route::resource('bank-accounts', \App\Http\Controllers\Admin\BankAccountController::class);
        Route::get('bank-accounts/{bank_account}/ledger', [\App\Http\Controllers\Admin\BankAccountController::class, 'ledger'])->name('bank-accounts.ledger');
        
        Route::resource('customers', \App\Http\Controllers\Admin\CustomerController::class);
        Route::resource('purchase-invoices', \App\Http\Controllers\Admin\PurchaseInvoiceController::class);
        Route::post('purchase-invoices/{purchase_invoice}/payment', [\App\Http\Controllers\Admin\PurchaseInvoiceController::class, 'recordPayment'])->name('purchase-invoices.payment');
        Route::resource('sales-invoices', \App\Http\Controllers\Admin\SalesInvoiceController::class);
        Route::post('sales-invoices/{sales_invoice}/payment', [\App\Http\Controllers\Admin\SalesInvoiceController::class, 'recordPayment'])->name('sales-invoices.payment');
        
        // Vehicle Rent Management
        Route::resource('vehicle-rents', \App\Http\Controllers\Admin\VehicleRentController::class);
        Route::post('vehicle-rents/validate', [\App\Http\Controllers\Admin\VehicleRentController::class, 'validateVehicleRent'])->name('vehicle-rents.validate');
        Route::get('vehicle-rents/export/excel', [\App\Http\Controllers\Admin\VehicleRentController::class, 'export'])->name('vehicle-rents.export');
        
        // Advance Payments
        Route::resource('advance-payments', \App\Http\Controllers\Admin\AdvancePaymentController::class);
        Route::post('advance-payments/validate', [\App\Http\Controllers\Admin\AdvancePaymentController::class, 'validateAdvancePayment'])->name('advance-payments.validate');
        Route::resource('payment-types', PaymentTypeController::class);

        // Salary Payments
        Route::resource('salary-payments', \App\Http\Controllers\Admin\SalaryPaymentController::class);
        Route::post('salary-payments/validate', [\App\Http\Controllers\Admin\SalaryPaymentController::class, 'validateSalaryPayment'])->name('salary-payments.validate');
        Route::post('salary-payments/{salaryPayment}/validate', [\App\Http\Controllers\Admin\SalaryPaymentController::class, 'validateSalaryPayment'])->name('salary-payments.validate.edit');
        Route::post('salary-payments/{salaryPayment}/record-payment', [\App\Http\Controllers\Admin\SalaryPaymentController::class, 'recordPayment'])->name('salary-payments.record-payment');
        Route::post('salary-payments/check-existing', [\App\Http\Controllers\Admin\SalaryPaymentController::class, 'checkExisting'])->name('salary-payments.check-existing');

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/financial-summary', [ReportController::class, 'financialSummary'])->name('reports.financial-summary');
        Route::get('/reports/income', [ReportController::class, 'incomeReport'])->name('reports.income');
        Route::get('/reports/expense', [ReportController::class, 'expenseReport'])->name('reports.expense');
        Route::get('/reports/project-materials', [ReportController::class, 'projectMaterialsReport'])->name('reports.project-materials');
        Route::get('/reports/project-materials/export', [ReportController::class, 'projectMaterialsExport'])->name('reports.project-materials.export');
        Route::get('/reports/staff-payment', [ReportController::class, 'staffPaymentReport'])->name('reports.staff-payment');
        Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');
        Route::get('/reports/trial-balance', [ReportController::class, 'trialBalance'])->name('reports.trial-balance');
        Route::get('/reports/general-ledger', [ReportController::class, 'generalLedger'])->name('reports.general-ledger');

        // Company Profile (accessible to all authenticated users for their own company)
        Route::get('/company/profile', [CompanyController::class, 'profile'])->name('companies.profile');
        Route::put('/company/profile', [CompanyController::class, 'profileUpdate'])->name('companies.profile.update');

        // Companies CRUD (super admin only)
        Route::middleware(['super_admin'])->group(function () {
            Route::resource('companies', CompanyController::class);
            Route::post('/companies/switch', [CompanyController::class, 'switch'])->name('companies.switch');

            // Users management (company-wise), super admin only
            Route::resource('users', UserController::class)->except(['show']);
            Route::post('users/validate', [UserController::class, 'validateUser'])->name('users.validate');
            Route::post('users/{user}/validate', [UserController::class, 'validateUser'])->name('users.validate.edit');
        });
    });
});

Route::get('construction-materials/export', [\App\Http\Controllers\Admin\ConstructionMaterialController::class, 'export'])->name('admin.construction-materials.export');
