<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesInvoice;
use App\Models\SalesInvoiceItem;
use App\Models\Customer;
use App\Models\Project;
use App\Models\BankAccount;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesInvoiceController extends Controller
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
        
        $query = SalesInvoice::where('company_id', $companyId)
            ->with(['customer', 'project', 'bankAccount'])
            ->orderBy('invoice_date', 'desc')
            ->orderBy('invoice_number', 'desc');
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        
        $invoices = $query->paginate(20);
        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        
        return view('admin.sales_invoices.index', compact('invoices', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)->where('status', '!=', 'cancelled')->orderBy('name')->get();
        $bankAccounts = BankAccount::where('company_id', $companyId)->where('is_active', true)->orderBy('account_name')->get();
        
        return view('admin.sales_invoices.create', compact('customers', 'projects', 'bankAccounts'));
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
            'customer_id' => 'nullable|exists:customers,id',
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
            
            $invoice = SalesInvoice::create([
                'company_id' => $companyId,
                'invoice_number' => SalesInvoice::generateInvoiceNumber(),
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
                'project_id' => $validated['project_id'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'bank_account_id' => $validated['bank_account_id'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'discount_amount' => $totalDiscount,
                'total_amount' => $totalAmount,
                'received_amount' => 0,
                'balance_amount' => $totalAmount,
                'status' => 'draft',
                'payment_status' => 'unpaid',
                'notes' => $validated['notes'] ?? null,
                'terms' => $validated['terms'] ?? null,
                'created_by' => auth()->id(),
            ]);
            
            foreach ($validated['items'] as $index => $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
                $taxAmount = $lineTotal * (($item['tax_rate'] ?? 0) / 100);
                
                SalesInvoiceItem::create([
                    'sales_invoice_id' => $invoice->id,
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
            
            return redirect()->route('admin.sales-invoices.show', $invoice)
                ->with('success', 'Sales invoice created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create sales invoice: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(SalesInvoice $salesInvoice)
    {
        $salesInvoice->load(['customer', 'project', 'bankAccount', 'items', 'creator']);
        return view('admin.sales_invoices.show', compact('salesInvoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalesInvoice $salesInvoice)
    {
        if ($salesInvoice->status !== 'draft') {
            return redirect()->route('admin.sales-invoices.show', $salesInvoice)
                ->with('error', 'Only draft invoices can be edited.');
        }
        
        $companyId = CompanyContext::getActiveCompanyId();
        
        $customers = Customer::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)->where('status', '!=', 'cancelled')->orderBy('name')->get();
        $bankAccounts = BankAccount::where('company_id', $companyId)->where('is_active', true)->orderBy('account_name')->get();
        
        $salesInvoice->load('items');
        
        return view('admin.sales_invoices.edit', compact('salesInvoice', 'customers', 'projects', 'bankAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalesInvoice $salesInvoice)
    {
        if ($salesInvoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be edited.');
        }
        
        $validated = $request->validate([
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'customer_id' => 'nullable|exists:customers,id',
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
            $balanceAmount = $totalAmount - $salesInvoice->received_amount;
            
            $salesInvoice->update([
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'customer_id' => $validated['customer_id'] ?? null,
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
            
            $salesInvoice->items()->delete();
            
            foreach ($validated['items'] as $index => $item) {
                $lineTotal = ($item['quantity'] * $item['unit_price']) - ($item['discount_amount'] ?? 0);
                $taxAmount = $lineTotal * (($item['tax_rate'] ?? 0) / 100);
                
                SalesInvoiceItem::create([
                    'sales_invoice_id' => $salesInvoice->id,
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
            
            return redirect()->route('admin.sales-invoices.show', $salesInvoice)
                ->with('success', 'Sales invoice updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update sales invoice: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalesInvoice $salesInvoice)
    {
        if ($salesInvoice->status !== 'draft') {
            return back()->with('error', 'Only draft invoices can be deleted.');
        }
        
        $salesInvoice->items()->delete();
        $salesInvoice->delete();
        
        return redirect()->route('admin.sales-invoices.index')
            ->with('success', 'Sales invoice deleted successfully.');
    }

    /**
     * Record payment for sales invoice.
     */
    public function recordPayment(Request $request, SalesInvoice $salesInvoice)
    {
        $validated = $request->validate([
            'payment_amount' => 'required|numeric|min:0.01|max:' . $salesInvoice->balance_amount,
            'payment_date' => 'required|date',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'notes' => 'nullable|string',
        ]);
        
        $newReceivedAmount = $salesInvoice->received_amount + $validated['payment_amount'];
        $newBalanceAmount = $salesInvoice->total_amount - $newReceivedAmount;
        
        $paymentStatus = 'unpaid';
        if ($newBalanceAmount <= 0.01) {
            $paymentStatus = 'paid';
        } elseif ($newReceivedAmount > 0) {
            $paymentStatus = 'partial';
        }
        
        $salesInvoice->update([
            'received_amount' => $newReceivedAmount,
            'balance_amount' => $newBalanceAmount,
            'payment_status' => $paymentStatus,
            'status' => $paymentStatus === 'paid' ? 'paid' : ($salesInvoice->status === 'draft' ? 'pending' : $salesInvoice->status),
        ]);
        
        // TODO: Create journal entry for payment
        
        return back()->with('success', 'Payment recorded successfully.');
    }
}
