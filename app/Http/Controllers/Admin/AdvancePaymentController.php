<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvancePayment;
use App\Models\Supplier;
use App\Models\Project;
use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\Category;
use App\Support\CompanyContext;
use Illuminate\Http\Request;

class AdvancePaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Get or create the "Advance Payments" category for expenses.
     */
    private function getOrCreateAdvancePaymentCategory($companyId)
    {
        $category = Category::where('company_id', $companyId)
            ->where('type', 'expense')
            ->where('name', 'Advance Payments')
            ->first();

        if (!$category) {
            $category = Category::create([
                'company_id' => $companyId,
                'name' => 'Advance Payments',
                'type' => 'expense',
                'description' => 'Advance payments to suppliers and vendors',
                'is_active' => true,
            ]);
        }

        return $category;
    }

    /**
     * Create expense entry for advance payment.
     */
    private function createExpenseFromAdvancePayment(AdvancePayment $advancePayment)
    {
        // Only create expense if no expense exists
        if (!$advancePayment->expense) {
            $companyId = $advancePayment->company_id;
            $category = $this->getOrCreateAdvancePaymentCategory($companyId);

            $paymentTypeLabel = $advancePayment->payment_type === 'vehicle_rent' 
                ? 'Vehicle Rent' 
                : ($advancePayment->payment_type === 'material_payment' ? 'Material Payment' : 'Advance Payment');

            $supplierName = $advancePayment->supplier ? $advancePayment->supplier->name : 'N/A';

            Expense::create([
                'company_id' => $companyId,
                'project_id' => $advancePayment->project_id,
                'advance_payment_id' => $advancePayment->id,
                'category_id' => $category->id,
                'expense_type' => 'purchase',
                'item_name' => "Advance Payment - {$paymentTypeLabel}",
                'description' => "Advance payment for {$paymentTypeLabel} - Supplier: {$supplierName}",
                'amount' => $advancePayment->amount,
                'date' => $advancePayment->payment_date,
                'payment_method' => $advancePayment->payment_method,
                'notes' => "Transaction Reference: {$advancePayment->transaction_reference}" . ($advancePayment->notes ? " | Notes: {$advancePayment->notes}" : ''),
                'created_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $query = AdvancePayment::where('company_id', $companyId)
            ->with(['project', 'supplier', 'bankAccount', 'creator']);
        
        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        
        // Filter by payment type
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        
        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('payment_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('payment_date', '<=', $request->end_date);
        }
        
        $advancePayments = $query->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        
        $suppliers = Supplier::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Calculate totals
        $totalAmount = $query->sum('amount');
        
        return view('admin.advance_payments.index', compact('advancePayments', 'projects', 'suppliers', 'totalAmount'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        
        $suppliers = Supplier::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $bankAccounts = BankAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get();
        
        return view('admin.advance_payments.create', compact('projects', 'suppliers', 'bankAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'payment_type' => 'required|in:vehicle_rent,material_payment',
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payment_method' => 'nullable|string|max:255',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        
        $validated['company_id'] = $companyId;
        $validated['created_by'] = auth()->id();
        
        $advancePayment = AdvancePayment::create($validated);

        // Auto-create expense entry
        $this->createExpenseFromAdvancePayment($advancePayment);
        
        return redirect()->route('admin.advance-payments.index')
            ->with('success', 'Advance payment recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AdvancePayment $advancePayment)
    {
        $advancePayment->load(['project', 'supplier', 'bankAccount', 'creator', 'updater', 'expense']);
        return view('admin.advance_payments.show', compact('advancePayment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdvancePayment $advancePayment)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        
        $suppliers = Supplier::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $bankAccounts = BankAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get();
        
        return view('admin.advance_payments.edit', compact('advancePayment', 'projects', 'suppliers', 'bankAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdvancePayment $advancePayment)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'payment_type' => 'required|in:vehicle_rent,material_payment',
            'supplier_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payment_method' => 'nullable|string|max:255',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        
        $validated['updated_by'] = auth()->id();
        
        $advancePayment->update($validated);

        // Refresh the model to get updated values
        $advancePayment->refresh();

        // Auto-create expense entry if it doesn't exist
        $this->createExpenseFromAdvancePayment($advancePayment);

        // Update existing expense if it exists
        if ($advancePayment->expense) {
            $expense = $advancePayment->expense;
            $paymentTypeLabel = $advancePayment->payment_type === 'vehicle_rent' 
                ? 'Vehicle Rent' 
                : ($advancePayment->payment_type === 'material_payment' ? 'Material Payment' : 'Advance Payment');
            $supplierName = $advancePayment->supplier ? $advancePayment->supplier->name : 'N/A';

            $expense->update([
                'amount' => $advancePayment->amount,
                'date' => $advancePayment->payment_date,
                'payment_method' => $advancePayment->payment_method,
                'item_name' => "Advance Payment - {$paymentTypeLabel}",
                'description' => "Advance payment for {$paymentTypeLabel} - Supplier: {$supplierName}",
                'notes' => "Transaction Reference: {$advancePayment->transaction_reference}" . ($advancePayment->notes ? " | Notes: {$advancePayment->notes}" : ''),
                'updated_by' => auth()->id(),
            ]);
        }
        
        return redirect()->route('admin.advance-payments.index')
            ->with('success', 'Advance payment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AdvancePayment $advancePayment)
    {
        
        $advancePayment->delete();
        
        return redirect()->route('admin.advance-payments.index')
            ->with('success', 'Advance payment deleted successfully.');
    }
}
