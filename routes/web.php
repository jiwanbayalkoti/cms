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
    if (Auth::check() && (Auth::user()->is_admin || in_array(Auth::user()->role, ['super_admin','admin']))) {
        return redirect()->route('admin.dashboard');
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
        
        // Categories CRUD
        Route::resource('categories', CategoryController::class);
        
        // Subcategories CRUD
        Route::resource('subcategories', SubcategoryController::class);
        
        // Staff CRUD
        Route::resource('staff', StaffController::class);
        
        // Positions CRUD
        Route::resource('positions', PositionController::class);
        
        // Projects CRUD
        Route::resource('projects', ProjectController::class);
        
        // Construction Materials CRUD
        Route::resource('construction-materials', ConstructionMaterialController::class);
        Route::resource('material-categories', MaterialCategoryController::class)->except(['show']);
        Route::resource('material-units', MaterialUnitController::class)->except(['show']);
        Route::resource('suppliers', SupplierController::class)->except(['show']);
        Route::resource('work-types', WorkTypeController::class)->except(['show']);
        Route::resource('material-names', \App\Http\Controllers\Admin\MaterialNameController::class)->except(['show']);
        Route::resource('payment-modes', \App\Http\Controllers\Admin\PaymentModeController::class)->except(['show']);
        Route::resource('purchased-bies', \App\Http\Controllers\Admin\PurchasedByController::class)->except(['show']);
        
        // Income CRUD
        Route::resource('incomes', IncomeController::class);
        
        // Expenses CRUD
        Route::resource('expenses', ExpenseController::class);
        
        // Bill Modules (Construction Final Bill / Estimate)
        Route::resource('bill-modules', BillModuleController::class);
        Route::resource('bill-categories', \App\Http\Controllers\Admin\BillCategoryController::class)->except(['show']);
        Route::resource('bill-subcategories', \App\Http\Controllers\Admin\BillSubcategoryController::class)->except(['show']);
        Route::post('bill-modules/{bill_module}/submit', [BillModuleController::class, 'submit'])->name('bill-modules.submit');
        Route::post('bill-modules/{bill_module}/approve', [BillModuleController::class, 'approve'])->name('bill-modules.approve');
        Route::get('bill-modules/{bill_module}/export/excel', [BillModuleController::class, 'exportExcel'])->name('bill-modules.export.excel');
        Route::get('bill-modules/{bill_module}/export/pdf', [BillModuleController::class, 'exportPdf'])->name('bill-modules.export.pdf');
        Route::get('bill-modules/{bill_module}/report', [BillModuleController::class, 'report'])->name('bill-modules.report');

        // Material calculator
        Route::get('material-calculator', [MaterialCalculatorController::class, 'index'])->name('material-calculator.index');
        Route::post('material-calculator/export/excel', [MaterialCalculatorController::class, 'exportExcel'])->name('material-calculator.export.excel');
        Route::post('material-calculator/export/pdf', [MaterialCalculatorController::class, 'exportPdf'])->name('material-calculator.export.pdf');
        Route::post('material-calculator/save', [MaterialCalculatorController::class, 'save'])->name('material-calculator.save');
        
        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/financial-summary', [ReportController::class, 'financialSummary'])->name('reports.financial-summary');
        Route::get('/reports/income', [ReportController::class, 'incomeReport'])->name('reports.income');
        Route::get('/reports/expense', [ReportController::class, 'expenseReport'])->name('reports.expense');
        Route::get('/reports/project-materials', [ReportController::class, 'projectMaterialsReport'])->name('reports.project-materials');
        Route::get('/reports/project-materials/export', [ReportController::class, 'projectMaterialsExport'])->name('reports.project-materials.export');
        Route::get('/reports/staff-payment', [ReportController::class, 'staffPaymentReport'])->name('reports.staff-payment');
        Route::get('/reports/balance-sheet', [ReportController::class, 'balanceSheet'])->name('reports.balance-sheet');

        // Company Profile (accessible to all authenticated users for their own company)
        Route::get('/company/profile', [CompanyController::class, 'profile'])->name('companies.profile');
        Route::put('/company/profile', [CompanyController::class, 'profileUpdate'])->name('companies.profile.update');

        // Companies CRUD (super admin only)
        Route::middleware(['super_admin'])->group(function () {
            Route::resource('companies', CompanyController::class);
            Route::post('/companies/switch', [CompanyController::class, 'switch'])->name('companies.switch');

            // Users management (company-wise), super admin only
            Route::resource('users', UserController::class)->except(['show']);
        });
    });
});

Route::get('construction-materials/export', [\App\Http\Controllers\Admin\ConstructionMaterialController::class, 'export'])->name('admin.construction-materials.export');
