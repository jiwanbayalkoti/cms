<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AdvancePaymentExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Http\Controllers\Admin\Traits\HasProjectAccess;
use App\Models\AdvancePayment;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Supplier;
use App\Models\Subcontractor;
use App\Models\Project;
use App\Models\BankAccount;
use App\Models\Expense;
use App\Models\Category;
use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdvancePaymentController extends Controller
{
    use ValidatesForms, HasProjectAccess;

    private ?bool $hasBeneficiaryTypeColumn = null;
    
    public function __construct()
    {
        $this->middleware('admin');
    }
    
    /**
     * Validate advance payment form data (AJAX endpoint)
     */
    public function validateAdvancePayment(Request $request)
    {
        $rules = [
            'project_id' => 'nullable|exists:projects,id',
            'payment_type' => 'required|string',
            'beneficiary_type' => 'required|in:supplier,subcontractor',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'subcontractor_id' => 'nullable|exists:subcontractors,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payment_method' => 'nullable|string|max:255',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ];
        
        return $this->validateForm($request, $rules);
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
     * Human-readable label for advance payment type (expense line items).
     */
    private function advancePaymentTypeLabel(AdvancePayment $advancePayment): string
    {
        return match ($advancePayment->payment_type) {
            'vehicle_rent' => 'Vehicle Rent',
            'material_payment' => 'Material Payment',
            'supplier' => 'Supplier Payment',
            default => 'Advance Payment',
        };
    }

    private function resolveBeneficiary(AdvancePayment $advancePayment): array
    {
        $type = $advancePayment->beneficiary_type ?: ($advancePayment->subcontractor_id ? 'subcontractor' : 'supplier');
        if ($type === 'subcontractor') {
            return [
                'type' => 'subcontractor',
                'name' => $advancePayment->subcontractor?->name ?? 'N/A',
            ];
        }

        return [
            'type' => 'supplier',
            'name' => $advancePayment->supplier?->name ?? 'N/A',
        ];
    }

    private function supportsBeneficiaryTypeColumn(): bool
    {
        if ($this->hasBeneficiaryTypeColumn !== null) {
            return $this->hasBeneficiaryTypeColumn;
        }

        $this->hasBeneficiaryTypeColumn = Schema::hasColumn('advance_payments', 'beneficiary_type');
        return $this->hasBeneficiaryTypeColumn;
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

            $paymentTypeLabel = $this->advancePaymentTypeLabel($advancePayment);
            $beneficiary = $this->resolveBeneficiary($advancePayment);
            $beneficiaryName = $beneficiary['name'];
            $beneficiaryTypeLabel = $beneficiary['type'] === 'subcontractor' ? 'Sub-contractor' : 'Supplier';

            Expense::create([
                'company_id' => $companyId,
                'project_id' => $advancePayment->project_id,
                'advance_payment_id' => $advancePayment->id,
                'subcontractor_id' => $advancePayment->subcontractor_id,
                'category_id' => $category->id,
                'expense_type' => 'purchase',
                'item_name' => "Advance Payment - {$paymentTypeLabel}",
                'description' => "Advance payment for {$paymentTypeLabel} - {$beneficiaryTypeLabel}: {$beneficiaryName}",
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
            ->with(['project', 'supplier', 'subcontractor', 'bankAccount', 'creator']);
        
        // Filter by accessible projects
        $this->filterByAccessibleProjects($query, 'project_id');
        
        // Filter by project
        if ($request->filled('project_id')) {
            $projectId = (int) $request->project_id;
            // Verify user has access to this project
            if (!$this->canAccessProject($projectId)) {
                if ($request->ajax()) {
                    return response()->json(['error' => 'You do not have access to this project.'], 403);
                }
                abort(403, 'You do not have access to this project.');
            }
            $query->where('project_id', $projectId);
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
        
        $listQuery = (clone $query);
        [$sortColumn, $sortDir] = $this->applyAdvancePaymentListSorting($listQuery, $request);
        $advancePayments = $listQuery->paginate(15)->withQueryString();
        
        // Get only accessible projects
        $projects = $this->getAccessibleProjects();
        
        $suppliers = Supplier::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $subcontractors = Subcontractor::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        // Calculate totals (need to recalculate after pagination)
        $totalAmountQuery = AdvancePayment::where('company_id', $companyId);
        $this->filterByAccessibleProjects($totalAmountQuery, 'project_id');
        
        if ($request->filled('project_id')) {
            $projectId = (int) $request->project_id;
            if ($this->canAccessProject($projectId)) {
                $totalAmountQuery->where('project_id', $projectId);
            }
        }
        if ($request->filled('payment_type')) {
            $totalAmountQuery->where('payment_type', $request->payment_type);
        }
        if ($request->filled('supplier_id')) {
            $totalAmountQuery->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('start_date')) {
            $totalAmountQuery->where('payment_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $totalAmountQuery->where('payment_date', '<=', $request->end_date);
        }
        
        $totalAmount = $totalAmountQuery->sum('amount');
        
        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $advancePaymentsData = $advancePayments->map(function($payment) {
                $beneficiary = $this->resolveBeneficiary($payment);
                return [
                    'id' => $payment->id,
                    'payment_date' => $payment->payment_date->format('Y-m-d'),
                    'payment_type' => ucfirst(str_replace('_', ' ', $payment->payment_type)),
                    'reference' => 'N/A',
                    'project_name' => $payment->project ? $payment->project->name : 'N/A',
                    'beneficiary_type' => $beneficiary['type'],
                    'beneficiary_name' => $beneficiary['name'],
                    'supplier_name' => $payment->supplier ? $payment->supplier->name : 'N/A',
                    'amount' => number_format($payment->amount, 2),
                    'payment_method' => ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')),
                ];
            });
            
            $summaryData = null;
            if ($advancePayments->count() > 0) {
                $summaryData = [
                    'totalAmount' => number_format($totalAmount, 2),
                ];
            }
            
            return response()->json([
                'advancePayments' => $advancePaymentsData,
                'pagination' => $advancePayments->links()->render(),
                'summary' => $summaryData,
                'currentPage' => $advancePayments->currentPage(),
                'perPage' => $advancePayments->perPage(),
                'sort' => $sortColumn,
                'sort_dir' => $sortDir,
            ]);
        }
        
        return view('admin.advance_payments.index', compact(
            'advancePayments',
            'projects',
            'suppliers',
            'subcontractors',
            'totalAmount',
            'sortColumn',
            'sortDir'
        ));
    }

    /**
     * @return array{0: string, 1: string} [sortColumn, sortDir]
     */
    private function applyAdvancePaymentListSorting(Builder $query, Request $request): array
    {
        $sortColumn = $request->get('sort', 'payment_date');
        $sortDir = strtolower((string) $request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowed = ['payment_date', 'payment_type', 'project', 'supplier', 'amount', 'payment_method'];
        if (! in_array($sortColumn, $allowed, true)) {
            $sortColumn = 'payment_date';
        }
        $dirSql = $sortDir === 'asc' ? 'ASC' : 'DESC';

        switch ($sortColumn) {
            case 'project':
                $query->orderByRaw(
                    '(SELECT p.name FROM projects AS p WHERE p.id = advance_payments.project_id LIMIT 1) '.$dirSql
                );
                break;
            case 'supplier':
                $query->orderByRaw(
                    '(SELECT s.name FROM suppliers AS s WHERE s.id = advance_payments.supplier_id LIMIT 1) '.$dirSql
                );
                break;
            default:
                $query->orderBy('advance_payments.'.$sortColumn, $sortDir);
                break;
        }

        $query->orderBy('advance_payments.id', $sortDir === 'asc' ? 'asc' : 'desc');

        return [$sortColumn, $sortDir];
    }

    /**
     * Export advance payments to Excel.
     */
    public function exportExcel(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();

        $query = AdvancePayment::where('company_id', $companyId);

        $this->filterByAccessibleProjects($query, 'project_id');

        if ($request->filled('project_id')) {
            $projectId = (int) $request->project_id;
            if ($this->canAccessProject($projectId)) {
                $query->where('project_id', $projectId);
            }
        }
        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('start_date')) {
            $query->where('payment_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('payment_date', '<=', $request->end_date);
        }

        $filename = 'advance_payments_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new AdvancePaymentExport($query), $filename);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        // Get only accessible projects
        $projects = $this->getAccessibleProjects();
        
        $suppliers = Supplier::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $subcontractors = Subcontractor::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $bankAccounts = BankAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get();
        
        $paymentTypes = \App\Models\PaymentType::orderBy('name')->get();
        
        // Return JSON for AJAX requests
        if (request()->ajax()) {
            return response()->json([
                'projects' => $projects,
                'suppliers' => $suppliers,
                'subcontractors' => $subcontractors,
                'bankAccounts' => $bankAccounts,
                'paymentTypes' => $paymentTypes
            ]);
        }
        
        return redirect()->route('admin.advance-payments.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'payment_type' => 'required|string',
            'beneficiary_type' => 'required|in:supplier,subcontractor',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'subcontractor_id' => 'nullable|exists:subcontractors,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payment_method' => 'nullable|string|max:255',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        
        // Convert payment_type ID to code if it's numeric (backward compatibility)
        if (is_numeric($validated['payment_type'])) {
            $paymentType = \App\Models\PaymentType::find($validated['payment_type']);
            if ($paymentType) {
                $validated['payment_type'] = $paymentType->code ?? strtolower(str_replace(' ', '_', $paymentType->name));
            }
        }

        if ($validated['beneficiary_type'] === 'supplier') {
            if (empty($validated['supplier_id'])) {
                return response()->json(['success' => false, 'errors' => ['supplier_id' => ['Please select supplier.']]], 422);
            }
            $validated['subcontractor_id'] = null;
        } else {
            if (empty($validated['subcontractor_id'])) {
                return response()->json(['success' => false, 'errors' => ['subcontractor_id' => ['Please select sub-contractor.']]], 422);
            }
            $validated['supplier_id'] = null;
        }

        if (! $this->supportsBeneficiaryTypeColumn()) {
            unset($validated['beneficiary_type']);
        }
        
        $validated['company_id'] = $companyId;
        $validated['created_by'] = auth()->id();
        
        $advancePayment = AdvancePayment::create($validated);

        // Auto-create expense entry
        $this->createExpenseFromAdvancePayment($advancePayment);
        
        // Load relations for JSON response
        $advancePayment->load(['project', 'supplier', 'subcontractor', 'bankAccount']);
        
        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Advance payment recorded successfully.',
                'advancePayment' => $advancePayment
            ]);
        }
        
        return redirect()->route('admin.advance-payments.index')
            ->with('success', 'Advance payment recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AdvancePayment $advancePayment)
    {
        $advancePayment->load(['project', 'supplier', 'subcontractor', 'bankAccount', 'creator', 'updater', 'expense']);
        
        // Return JSON for AJAX requests
        if (request()->ajax()) {
            return response()->json([
                'advancePayment' => [
                    'id' => $advancePayment->id,
                    'payment_date' => $advancePayment->payment_date->format('F d, Y'),
                    'payment_type' => ucfirst(str_replace('_', ' ', $advancePayment->payment_type)),
                    'payment_type_raw' => $advancePayment->payment_type,
                    'beneficiary_type' => $advancePayment->beneficiary_type ?: ($advancePayment->subcontractor_id ? 'subcontractor' : 'supplier'),
                    'project' => $advancePayment->project ? $advancePayment->project->name : 'N/A',
                    'supplier' => $advancePayment->supplier ? $advancePayment->supplier->name : 'N/A',
                    'subcontractor' => $advancePayment->subcontractor ? $advancePayment->subcontractor->name : 'N/A',
                    'amount' => number_format($advancePayment->amount, 2),
                    'payment_method' => ucfirst(str_replace('_', ' ', $advancePayment->payment_method ?? 'N/A')),
                    'bank_account' => $advancePayment->bankAccount ? $advancePayment->bankAccount->account_name : 'N/A',
                    'transaction_reference' => $advancePayment->transaction_reference ?? 'N/A',
                    'notes' => $advancePayment->notes ?? '',
                    'expense' => $advancePayment->expense ? [
                        'id' => $advancePayment->expense->id,
                        'created_at' => $advancePayment->expense->created_at->format('Y-m-d H:i')
                    ] : null,
                    'creator' => $advancePayment->creator ? $advancePayment->creator->name : 'N/A',
                    'created_at' => $advancePayment->created_at->format('Y-m-d H:i'),
                    'updater' => $advancePayment->updater ? $advancePayment->updater->name : null,
                    'updated_at' => $advancePayment->updated_at->format('Y-m-d H:i')
                ]
            ]);
        }
        
        return redirect()->route('admin.advance-payments.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AdvancePayment $advancePayment)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        // Get only accessible projects
        $projects = $this->getAccessibleProjects();
        
        $suppliers = Supplier::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $subcontractors = Subcontractor::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        
        $bankAccounts = BankAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('account_name')
            ->get();
        
        $paymentTypes = \App\Models\PaymentType::orderBy('name')->get();
        
        // Return JSON for AJAX requests
        if (request()->ajax()) {
            return response()->json([
                'advancePayment' => $advancePayment,
                'projects' => $projects,
                'suppliers' => $suppliers,
                'subcontractors' => $subcontractors,
                'bankAccounts' => $bankAccounts,
                'paymentTypes' => $paymentTypes
            ]);
        }
        
        return redirect()->route('admin.advance-payments.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AdvancePayment $advancePayment)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'payment_type' => 'required|string',
            'beneficiary_type' => 'required|in:supplier,subcontractor',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'subcontractor_id' => 'nullable|exists:subcontractors,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'payment_method' => 'nullable|string|max:255',
            'transaction_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        
        // Convert payment_type ID to code if it's numeric (backward compatibility)
        if (is_numeric($validated['payment_type'])) {
            $paymentType = \App\Models\PaymentType::find($validated['payment_type']);
            if ($paymentType) {
                $validated['payment_type'] = $paymentType->code ?? strtolower(str_replace(' ', '_', $paymentType->name));
            }
        }

        if ($validated['beneficiary_type'] === 'supplier') {
            if (empty($validated['supplier_id'])) {
                return response()->json(['success' => false, 'errors' => ['supplier_id' => ['Please select supplier.']]], 422);
            }
            $validated['subcontractor_id'] = null;
        } else {
            if (empty($validated['subcontractor_id'])) {
                return response()->json(['success' => false, 'errors' => ['subcontractor_id' => ['Please select sub-contractor.']]], 422);
            }
            $validated['supplier_id'] = null;
        }

        if (! $this->supportsBeneficiaryTypeColumn()) {
            unset($validated['beneficiary_type']);
        }
        
        $validated['updated_by'] = auth()->id();
        
        $advancePayment->update($validated);

        // Refresh the model to get updated values
        $advancePayment->refresh();

        // Auto-create expense entry if it doesn't exist
        $this->createExpenseFromAdvancePayment($advancePayment);

        // Update existing expense if it exists
        if ($advancePayment->expense) {
            $expense = $advancePayment->expense;
            $paymentTypeLabel = $this->advancePaymentTypeLabel($advancePayment);
            $beneficiary = $this->resolveBeneficiary($advancePayment);
            $beneficiaryName = $beneficiary['name'];
            $beneficiaryTypeLabel = $beneficiary['type'] === 'subcontractor' ? 'Sub-contractor' : 'Supplier';

            $expense->update([
                'subcontractor_id' => $advancePayment->subcontractor_id,
                'amount' => $advancePayment->amount,
                'date' => $advancePayment->payment_date,
                'payment_method' => $advancePayment->payment_method,
                'item_name' => "Advance Payment - {$paymentTypeLabel}",
                'description' => "Advance payment for {$paymentTypeLabel} - {$beneficiaryTypeLabel}: {$beneficiaryName}",
                'notes' => "Transaction Reference: {$advancePayment->transaction_reference}" . ($advancePayment->notes ? " | Notes: {$advancePayment->notes}" : ''),
                'updated_by' => auth()->id(),
            ]);
        }
        
        // Load relations for JSON response
        $advancePayment->load(['project', 'supplier', 'subcontractor', 'bankAccount']);
        
        // Return JSON for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Advance payment updated successfully.',
                'advancePayment' => $advancePayment
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
        
        // Return JSON for AJAX requests
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Advance payment deleted successfully.'
            ]);
        }
        
        return redirect()->route('admin.advance-payments.index')
            ->with('success', 'Advance payment deleted successfully.');
    }
}
