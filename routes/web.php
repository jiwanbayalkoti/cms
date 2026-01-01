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
        // Redirect admin/super_admin to dashboard, others to projects
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('admin.projects.index');
        }
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
    Route::middleware(['admin', 'site_engineer'])->group(function () {
        // Dashboard (Admin only - not accessible to regular users)
        Route::middleware(['admin_only'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        });
        
        // Super Admin Dashboard
        Route::middleware(['super_admin'])->group(function () {
            Route::get('/super-admin/dashboard', [DashboardController::class, 'superAdminDashboard'])->name('super-admin.dashboard');
        });
        
        // Categories CRUD (with rate limiting for form submissions)
        Route::middleware(['throttle:forms'])->group(function () {
            Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
            Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::patch('categories/{category}', [CategoryController::class, 'update']);
            Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        });
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::get('categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::post('categories/validate', [CategoryController::class, 'validateCategory'])->name('categories.validate');
        Route::get('categories/{category}/subcategories', [CategoryController::class, 'getSubcategories'])->name('categories.subcategories');
        
        // Subcategories CRUD (with rate limiting for form submissions)
        Route::middleware(['throttle:forms'])->group(function () {
            Route::post('subcategories', [SubcategoryController::class, 'store'])->name('subcategories.store');
            Route::put('subcategories/{subcategory}', [SubcategoryController::class, 'update'])->name('subcategories.update');
            Route::patch('subcategories/{subcategory}', [SubcategoryController::class, 'update']);
            Route::delete('subcategories/{subcategory}', [SubcategoryController::class, 'destroy'])->name('subcategories.destroy');
        });
        Route::get('subcategories', [SubcategoryController::class, 'index'])->name('subcategories.index');
        Route::get('subcategories/create', [SubcategoryController::class, 'create'])->name('subcategories.create');
        Route::get('subcategories/{subcategory}', [SubcategoryController::class, 'show'])->name('subcategories.show');
        Route::get('subcategories/{subcategory}/edit', [SubcategoryController::class, 'edit'])->name('subcategories.edit');
        Route::post('subcategories/validate', [SubcategoryController::class, 'validateSubcategory'])->name('subcategories.validate');
        
        // Staff CRUD (Admin only - not accessible to regular users)
        Route::middleware(['admin_only'])->group(function () {
            Route::get('staff', [StaffController::class, 'index'])->name('staff.index');
            Route::get('staff/create', [StaffController::class, 'create'])->name('staff.create');
            Route::get('staff/{staff}', [StaffController::class, 'show'])->name('staff.show');
            Route::get('staff/{staff}/edit', [StaffController::class, 'edit'])->name('staff.edit');
            Route::middleware(['throttle:forms'])->group(function () {
                Route::post('staff', [StaffController::class, 'store'])->name('staff.store');
                Route::put('staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
                Route::patch('staff/{staff}', [StaffController::class, 'update']);
                Route::delete('staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');
            });
            Route::post('staff/validate', [StaffController::class, 'validateStaff'])->name('staff.validate');
            Route::post('staff/{staff}/validate', [StaffController::class, 'validateStaff'])->name('staff.validate.edit');
            Route::get('staff/{staff}/details', [StaffController::class, 'getDetails'])->name('staff.details');
            
            // Positions CRUD
            Route::get('positions', [PositionController::class, 'index'])->name('positions.index');
            Route::get('positions/create', [PositionController::class, 'create'])->name('positions.create');
            Route::get('positions/{position}', [PositionController::class, 'show'])->name('positions.show');
            Route::get('positions/{position}/edit', [PositionController::class, 'edit'])->name('positions.edit');
            Route::middleware(['throttle:forms'])->group(function () {
                Route::post('positions', [PositionController::class, 'store'])->name('positions.store');
                Route::put('positions/{position}', [PositionController::class, 'update'])->name('positions.update');
                Route::patch('positions/{position}', [PositionController::class, 'update']);
                Route::delete('positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');
            });
        });
        
        // Projects - View and Gallery (accessible to all authenticated users)
        Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
        
        // Projects - Create, Edit, Delete (Admin only) - Must be before {project} route
        Route::middleware(['admin_only'])->group(function () {
            Route::get('projects/create', [ProjectController::class, 'create'])->name('projects.create');
            Route::middleware(['throttle:uploads'])->group(function () {
                Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
                Route::put('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
                Route::patch('projects/{project}', [ProjectController::class, 'update'])->name('projects.update');
            });
            Route::get('projects/{project}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
            Route::middleware(['throttle:forms'])->group(function () {
                Route::delete('projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
            });
        });
        
        Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::get('projects/{project}/gallery', [ProjectController::class, 'gallery'])->name('projects.gallery');
        Route::middleware(['throttle:uploads'])->group(function () {
            Route::post('projects/{project}/gallery/album', [ProjectController::class, 'addAlbum'])->name('projects.gallery.album.add');
            Route::put('projects/{project}/gallery/album/{albumIndex}', [ProjectController::class, 'updateAlbum'])->name('projects.gallery.album.update');
            Route::post('projects/{project}/gallery/album/{albumIndex}/photos', [ProjectController::class, 'addPhotos'])->name('projects.gallery.photos.add');
        });
        Route::middleware(['throttle:forms'])->group(function () {
            Route::delete('projects/{project}/gallery/album/{albumIndex}', [ProjectController::class, 'deleteAlbum'])->name('projects.gallery.album.delete');
            Route::post('projects/{project}/gallery/album/{albumIndex}/photos/bulk-approve', [ProjectController::class, 'bulkApprovePhotos'])->name('projects.gallery.photos.bulk-approve');
            Route::post('projects/{projectId}/gallery/album/{albumIndex}/photo/{photoIndex}/approve', [ProjectController::class, 'approvePhoto'])->name('projects.gallery.photo.approve');
            Route::post('projects/{projectId}/gallery/album/{albumIndex}/photo/{photoIndex}/disapprove', [ProjectController::class, 'disapprovePhoto'])->name('projects.gallery.photo.disapprove');
            Route::delete('projects/{project}/gallery/album/{albumIndex}/photo/{photoIndex}', [ProjectController::class, 'deletePhoto'])->name('projects.gallery.photo.delete');
            Route::post('projects/{project}/gallery/album/{albumIndex}/photo/{photoIndex}', [ProjectController::class, 'deletePhoto'])->name('projects.gallery.photo.delete.post');
        });
        Route::post('projects/validate', [ProjectController::class, 'validateProjectForm'])->name('projects.validate');
        Route::post('projects/switch', [ProjectController::class, 'switch'])->name('projects.switch');
        
        // Construction Materials CRUD
        Route::get('construction-materials', [ConstructionMaterialController::class, 'index'])->name('construction-materials.index');
        Route::get('construction-materials/create', [ConstructionMaterialController::class, 'create'])->name('construction-materials.create');
        Route::get('construction-materials/{construction_material}', [ConstructionMaterialController::class, 'show'])->name('construction-materials.show');
        Route::get('construction-materials/{construction_material}/edit', [ConstructionMaterialController::class, 'edit'])->name('construction-materials.edit');
        Route::middleware(['throttle:forms'])->group(function () {
            Route::post('construction-materials', [ConstructionMaterialController::class, 'store'])->name('construction-materials.store');
            Route::put('construction-materials/{construction_material}', [ConstructionMaterialController::class, 'update'])->name('construction-materials.update');
            Route::patch('construction-materials/{construction_material}', [ConstructionMaterialController::class, 'update']);
            Route::delete('construction-materials/{construction_material}', [ConstructionMaterialController::class, 'destroy'])->name('construction-materials.destroy');
            Route::get('construction-materials/{construction_material}/clone', [ConstructionMaterialController::class, 'clone'])->name('construction-materials.clone');
        });
        Route::post('construction-materials/validate', [ConstructionMaterialController::class, 'validateMaterial'])->name('construction-materials.validate');
        Route::resource('material-categories', MaterialCategoryController::class)->except(['show']);
        Route::resource('material-units', MaterialUnitController::class)->except(['show']);
        Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
        Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::middleware(['throttle:uploads'])->group(function () {
            Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
            Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
            Route::patch('suppliers/{supplier}', [SupplierController::class, 'update']);
        });
        Route::middleware(['throttle:forms'])->group(function () {
            Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
        });
        Route::post('suppliers/validate', [SupplierController::class, 'validateSupplier'])->name('suppliers.validate');
        Route::post('suppliers/{supplier}/validate', [SupplierController::class, 'validateSupplier'])->name('suppliers.validate.edit');
        Route::resource('work-types', WorkTypeController::class)->except(['show']);
        Route::resource('material-names', \App\Http\Controllers\Admin\MaterialNameController::class);
        Route::resource('payment-modes', \App\Http\Controllers\Admin\PaymentModeController::class)->except(['show']);
        Route::resource('purchased-bies', \App\Http\Controllers\Admin\PurchasedByController::class)->except(['show']);
        
        // Income CRUD (Admin only - not accessible to regular users)
        Route::middleware(['admin_only'])->group(function () {
            Route::get('incomes', [IncomeController::class, 'index'])->name('incomes.index');
            Route::get('incomes/create', [IncomeController::class, 'create'])->name('incomes.create');
            Route::get('incomes/{income}', [IncomeController::class, 'show'])->name('incomes.show');
            Route::get('incomes/{income}/edit', [IncomeController::class, 'edit'])->name('incomes.edit');
            Route::middleware(['throttle:forms'])->group(function () {
                Route::post('incomes', [IncomeController::class, 'store'])->name('incomes.store');
                Route::put('incomes/{income}', [IncomeController::class, 'update'])->name('incomes.update');
                Route::patch('incomes/{income}', [IncomeController::class, 'update']);
                Route::delete('incomes/{income}', [IncomeController::class, 'destroy'])->name('incomes.destroy');
            });
            Route::post('incomes/validate', [IncomeController::class, 'validateIncome'])->name('incomes.validate');
        });
        
        // Expenses CRUD
        Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/create', [ExpenseController::class, 'create'])->name('expenses.create');
        Route::get('expenses/{expense}', [ExpenseController::class, 'show'])->name('expenses.show');
        Route::get('expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::middleware(['throttle:uploads'])->group(function () {
            Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
            Route::put('expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
            Route::patch('expenses/{expense}', [ExpenseController::class, 'update']);
        });
        Route::middleware(['throttle:forms'])->group(function () {
            Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
            Route::get('expenses/{expense}/clone', [ExpenseController::class, 'clone'])->name('expenses.clone');
        });
        Route::post('expenses/validate', [ExpenseController::class, 'validateExpense'])->name('expenses.validate');
        Route::resource('expense-types', ExpenseTypeController::class);
        
        // Bill Modules (Construction Final Bill / Estimate) (Admin only - not accessible to regular users)
        Route::middleware(['admin_only'])->group(function () {
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
        });

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
        Route::get('vehicle-rents', [\App\Http\Controllers\Admin\VehicleRentController::class, 'index'])->name('vehicle-rents.index');
        Route::get('vehicle-rents/create', [\App\Http\Controllers\Admin\VehicleRentController::class, 'create'])->name('vehicle-rents.create');
        Route::get('vehicle-rents/{vehicle_rent}', [\App\Http\Controllers\Admin\VehicleRentController::class, 'show'])->name('vehicle-rents.show');
        Route::get('vehicle-rents/{vehicle_rent}/edit', [\App\Http\Controllers\Admin\VehicleRentController::class, 'edit'])->name('vehicle-rents.edit');
        Route::middleware(['throttle:forms'])->group(function () {
            Route::post('vehicle-rents', [\App\Http\Controllers\Admin\VehicleRentController::class, 'store'])->name('vehicle-rents.store');
            Route::put('vehicle-rents/{vehicle_rent}', [\App\Http\Controllers\Admin\VehicleRentController::class, 'update'])->name('vehicle-rents.update');
            Route::patch('vehicle-rents/{vehicle_rent}', [\App\Http\Controllers\Admin\VehicleRentController::class, 'update']);
            Route::delete('vehicle-rents/{vehicle_rent}', [\App\Http\Controllers\Admin\VehicleRentController::class, 'destroy'])->name('vehicle-rents.destroy');
        });
        Route::post('vehicle-rents/validate', [\App\Http\Controllers\Admin\VehicleRentController::class, 'validateVehicleRent'])->name('vehicle-rents.validate');
        Route::get('vehicle-rents/export/excel', [\App\Http\Controllers\Admin\VehicleRentController::class, 'export'])->name('vehicle-rents.export');
        
        // Advance Payments (Admin only - not accessible to regular users)
        Route::middleware(['admin_only'])->group(function () {
            Route::get('advance-payments', [\App\Http\Controllers\Admin\AdvancePaymentController::class, 'index'])->name('advance-payments.index');
            Route::get('advance-payments/create', [\App\Http\Controllers\Admin\AdvancePaymentController::class, 'create'])->name('advance-payments.create');
            Route::get('advance-payments/{advance_payment}', [\App\Http\Controllers\Admin\AdvancePaymentController::class, 'show'])->name('advance-payments.show');
            Route::get('advance-payments/{advance_payment}/edit', [\App\Http\Controllers\Admin\AdvancePaymentController::class, 'edit'])->name('advance-payments.edit');
            Route::middleware(['throttle:forms'])->group(function () {
                Route::post('advance-payments', [\App\Http\Controllers\Admin\AdvancePaymentController::class, 'store'])->name('advance-payments.store');
                Route::put('advance-payments/{advance_payment}', [\App\Http\Controllers\Admin\AdvancePaymentController::class, 'update'])->name('advance-payments.update');
                Route::patch('advance-payments/{advance_payment}', [\App\Http\Controllers\Admin\AdvancePaymentController::class, 'update']);
                Route::delete('advance-payments/{advance_payment}', [\App\Http\Controllers\Admin\AdvancePaymentController::class, 'destroy'])->name('advance-payments.destroy');
            });
            Route::post('advance-payments/validate', [\App\Http\Controllers\Admin\AdvancePaymentController::class, 'validateAdvancePayment'])->name('advance-payments.validate');
        });
        Route::resource('payment-types', PaymentTypeController::class);

        // Salary Payments (Admin only - not accessible to regular users)
        Route::middleware(['admin_only'])->group(function () {
            Route::get('salary-payments', [\App\Http\Controllers\Admin\SalaryPaymentController::class, 'index'])->name('salary-payments.index');
            Route::get('salary-payments/create', [\App\Http\Controllers\Admin\SalaryPaymentController::class, 'create'])->name('salary-payments.create');
            Route::get('salary-payments/{salary_payment}', [\App\Http\Controllers\Admin\SalaryPaymentController::class, 'show'])->name('salary-payments.show');
            Route::get('salary-payments/{salary_payment}/edit', [\App\Http\Controllers\Admin\SalaryPaymentController::class, 'edit'])->name('salary-payments.edit');
            Route::middleware(['throttle:forms'])->group(function () {
                Route::post('salary-payments', [\App\Http\Controllers\Admin\SalaryPaymentController::class, 'store'])->name('salary-payments.store');
                Route::put('salary-payments/{salary_payment}', [\App\Http\Controllers\Admin\SalaryPaymentController::class, 'update'])->name('salary-payments.update');
                Route::patch('salary-payments/{salary_payment}', [\App\Http\Controllers\Admin\SalaryPaymentController::class, 'update']);
                Route::delete('salary-payments/{salary_payment}', [\App\Http\Controllers\Admin\SalaryPaymentController::class, 'destroy'])->name('salary-payments.destroy');
                Route::post('salary-payments/{salaryPayment}/record-payment', [\App\Http\Controllers\Admin\SalaryPaymentController::class, 'recordPayment'])->name('salary-payments.record-payment');
            });
            Route::post('salary-payments/validate', [\App\Http\Controllers\Admin\SalaryPaymentController::class, 'validateSalaryPayment'])->name('salary-payments.validate');
            Route::post('salary-payments/{salaryPayment}/validate', [\App\Http\Controllers\Admin\SalaryPaymentController::class, 'validateSalaryPayment'])->name('salary-payments.validate.edit');
            Route::post('salary-payments/check-existing', [\App\Http\Controllers\Admin\SalaryPaymentController::class, 'checkExisting'])->name('salary-payments.check-existing');
        });

        // Reports (Admin only - not accessible to regular users)
        Route::middleware(['admin_only'])->group(function () {
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
        });

        // Company Profile (Admin only - not accessible to regular users)
        Route::middleware(['admin_only'])->group(function () {
        Route::get('/company/profile', [CompanyController::class, 'profile'])->name('companies.profile');
        Route::middleware(['throttle:uploads'])->group(function () {
            Route::put('/company/profile', [CompanyController::class, 'profileUpdate'])->name('companies.profile.update');
        });
        });

        // Users management (Admin and Super Admin can access)
        Route::middleware(['admin_only'])->group(function () {
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::middleware(['throttle:forms'])->group(function () {
                Route::post('users', [UserController::class, 'store'])->name('users.store');
                Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
                Route::patch('users/{user}', [UserController::class, 'update']);
                Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            });
            Route::post('users/validate', [UserController::class, 'validateUser'])->name('users.validate');
            Route::post('users/{user}/validate', [UserController::class, 'validateUser'])->name('users.validate.edit');
        });

        // Companies CRUD (super admin only)
        Route::middleware(['super_admin'])->group(function () {
            Route::get('companies', [CompanyController::class, 'index'])->name('companies.index');
            Route::get('companies/create', [CompanyController::class, 'create'])->name('companies.create');
            Route::get('companies/{company}/edit', [CompanyController::class, 'edit'])->name('companies.edit');
            Route::get('companies/{company}', [CompanyController::class, 'show'])->name('companies.show');
            Route::middleware(['throttle:uploads'])->group(function () {
                Route::post('companies', [CompanyController::class, 'store'])->name('companies.store');
                Route::put('companies/{company}', [CompanyController::class, 'update'])->name('companies.update');
                Route::patch('companies/{company}', [CompanyController::class, 'update']);
            });
            Route::middleware(['throttle:forms'])->group(function () {
                Route::delete('companies/{company}', [CompanyController::class, 'destroy'])->name('companies.destroy');
            });
            Route::post('/companies/switch', [CompanyController::class, 'switch'])->name('companies.switch');
        });
    });
});

Route::get('construction-materials/export', [\App\Http\Controllers\Admin\ConstructionMaterialController::class, 'export'])->name('admin.construction-materials.export');
