<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\Supplier;
use App\Models\Project;
use App\Models\BankAccount;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $query = PurchaseInvoice::where('company_id', $companyId)
            ->with(['vendor', 'project', 'bankAccount'])
            ->orderBy('invoice_date', 'desc')
            ->orderBy('invoice_number', 'desc');
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }
        
        $invoices = $query->paginate(20);
        $vendors = Supplier::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        
        return view('admin.purchase_invoices.index', compact('invoices', 'vendors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $vendors = Supplier::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)->where('status', '!=', 'cancelled')->orderBy('name')->get();
        $bankAccounts = BankAccount::where('company_id', $companyId)->where('is_active', true)->orderBy('account_name')->get();
        
        return view('admin.purchase_invoices.create', compact('vendors', 'projects', 'bankAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'vendor_id' => 'nullable|exists:suppliers,id',
            'project_id' => 'nullable|exists:projects,id',
            'reference_number' => 'nullable|string|max:100',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ]);
        
        DB::beginTransaction();
        try {
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;
            
            // Calculate totals
            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
                $taxAmount = $lineTotal * (($item['tax_rate'] ?? 0) / 100);
                $subtotal += $lineTotal;
                $totalTax += $taxAmount;
                $totalDiscount += ($item['discount_amount'] ?? 0);
            }
            
            $totalAmount = $subtotal + $totalTax;
            
            $invoice = PurchaseInvoice::create([
                'company_id' => $companyId,
                'invoice_number' => PurchaseInvoice::generateInvoiceNumber(),
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'vendor_id' => $validated['vendor_id'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'bank_account_id' => $validated['bank_account_id'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount_amount' => $totalDiscount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'balance_amount' => $totalAmount,
                'status' => 'draft',
                'payment_status' => 'unpaid',
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'created_by' => auth()->id(),
            ]);
            
            // Create items
            foreach ($validated['items'] as $index => $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
                $taxAmount = $lineTotal * (($item['tax_rate'] ?? 0) / 100);
                
                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? null,
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'line_total' => $lineTotal + $taxAmount,
                    'line_number' => $index + 1,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('admin.purchase-invoices.show', $invoice)
                ->with('success', 'Purchase invoice created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create purchase invoice: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseInvoice $purchaseInvoice)
    {
        $purchaseInvoice->load(['vendor', 'project', 'bankAccount', 'items', 'creator']);
        return view('admin.purchase_invoices.show', compact('purchaseInvoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseInvoice $purchaseInvoice)
    {
        if ($purchaseInvoice->status !== 'draft') {
            return redirect()->route('admin.purchase-invoices.show', $purchaseInvoice)
                ->with('error', 'Only draft invoices can be edited.');
        }
        
        $companyId = CompanyContext::getActiveCompanyId();
        
        $vendors = Supplier::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)->where('status', '!=', 'cancelled')->orderBy('name')->get();
        $bankAccounts = BankAccount::where('company_id', $companyId)->where('is_active', true)->orderBy('account_name')->get();
        
        $purchaseInvoice->load('items');
        
        return view('admin.purchase_invoices.edit', compact('purchaseInvoice', 'vendors', 'projects', 'bankAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        if ($purchaseInvoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be edited.');
        }
        
        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'vendor_id' => 'nullable|exists:suppliers,id',
            'project_id' => 'nullable|exists:projects,id',
            'reference_number' => 'nullable|string|max:100',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'items.*.discount_amount' => 'nullable|numeric|min:0',
        ]);
        
        DB::beginTransaction();
        try {
            $subtotal = 0;
            $totalTax = 0;
            $totalDiscount = 0;
            
            foreach ($validated['items'] as $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
                $taxAmount = $lineTotal * (($item['tax_rate'] ?? 0) / 100);
                $subtotal += $lineTotal;
                $totalTax += $taxAmount;
                $totalDiscount += ($item['discount_amount'] ?? 0);
            }
            
            $totalAmount = $subtotal + $totalTax;
            $balanceAmount = $totalAmount - $purchaseInvoice->paid_amount;
            
            $purchaseInvoice->update([
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'vendor_id' => $validated['vendor_id'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'bank_account_id' => $validated['bank_account_id'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount_amount' => $totalDiscount,
                'total_amount' => $totalAmount,
                'balance_amount' => $balanceAmount,
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'updated_by' => auth()->id(),
            ]);
            
            // Delete existing items
            $purchaseInvoice->items()->delete();
            
            // Create new items
            foreach ($validated['items'] as $index => $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
                $taxAmount = $lineTotal * (($item['tax_rate'] ?? 0) / 100);
                
                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $purchaseInvoice->id,
                    'item_name' => $item['item_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? null,
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $taxAmount,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'line_total' => $lineTotal + $taxAmount,
                    'line_number' => $index + 1,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('admin.purchase-invoices.show', $purchaseInvoice)
                ->with('success', 'Purchase invoice updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update purchase invoice: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseInvoice $purchaseInvoice)
    {
        if ($purchaseInvoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be deleted.');
        }
        
        $purchaseInvoice->items()->delete();
        $purchaseInvoice->delete();
        
        return redirect()->route('admin.purchase-invoices.index')
            ->with('success', 'Purchase invoice deleted successfully.');
    }

    /**
     * Record payment for purchase invoice.
     */
    public function recordPayment(Request $request, PurchaseInvoice $purchaseInvoice)
    {
        $validated = $request->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:' . $purchaseInvoice->balance_amount,
            'payment_date' => 'required|date',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'notes' => 'nullable|string',
        ]);
        
        $newPaidAmount = $purchaseInvoice->paid_amount + $validated['payment_amount'];
        $newBalanceAmount = $purchaseInvoice->total_amount - $newPaidAmount;
        
        $paymentStatus = 'unpaid';
        if ($newBalanceAmount <= 0.01) {
            $paymentStatus = 'paid';
        } elseif ($newPaidAmount > 0) {
            $paymentStatus = 'partial';
        }
        
        $purchaseInvoice->update([
            'paid_amount' => $newPaidAmount,
            'balance_amount' => $newBalanceAmount,
            'payment_status' => $paymentStatus,
            'status' => $paymentStatus === 'paid' ? 'paid' : ($purchaseInvoice->status === 'draft' ? 'pending' : $purchaseInvoice->status),
        ]);
        
        // TODO: Create journal entry for payment
        
        return back()->with('success', 'Payment recorded successfully.');
    }
}
