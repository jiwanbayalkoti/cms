<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Models\AdvancePayment;
use App\Models\ConstructionMaterial;
use App\Models\PurchaseInvoice;
use App\Models\Supplier;
use App\Models\VehicleRent;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupplierController extends Controller
{
    use ValidatesForms;
    
    public function __construct()
    {
        $this->middleware('admin');
    }
    
    /**
     * Validate supplier form data (AJAX endpoint)
     */
    public function validateSupplier(Request $request, Supplier $supplier = null)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $nameRule = $supplier
            ? 'required|string|max:255|unique:suppliers,name,' . $supplier->id . ',id,company_id,' . $companyId
            : 'required|string|max:255|unique:suppliers,name,NULL,id,company_id,' . $companyId;
        
        $rules = [
            'name' => $nameRule,
            'contact' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'account_holder_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'branch_address' => 'nullable|string',
            'qr_code_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'sometimes|boolean',
        ];
        
        return $this->validateForm($request, $rules);
    }

    public function index()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $sortColumn = request('sort', 'name');
        $sortDir = strtolower((string) request('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['name', 'contact', 'email', 'is_active', 'created_at'];
        if (!in_array($sortColumn, $allowedSorts, true)) {
            $sortColumn = 'name';
        }

        $suppliers = Supplier::where('company_id', $companyId)
            ->orderBy($sortColumn, $sortDir)
            ->orderBy('id', $sortDir === 'asc' ? 'asc' : 'desc')
            ->paginate(15)
            ->withQueryString();

        $supplierIds = $suppliers->getCollection()->pluck('id');
        $advancePaymentRows = AdvancePayment::where('company_id', $companyId)
            ->whereIn('supplier_id', $supplierIds)
            ->with(['project', 'bankAccount'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get()
            ->map(function ($p) {
                return [
                    'supplier_id' => (int) $p->supplier_id,
                    'payment_date' => optional($p->payment_date)->format('Y-m-d'),
                    'payment_label' => ucfirst(str_replace('_', ' ', $p->payment_type ?? '')),
                    'project_name' => $p->project->name ?? 'N/A',
                    'amount' => (float) ($p->amount ?? 0),
                    'payment_method_label' => ucfirst(str_replace('_', ' ', $p->payment_method ?? 'N/A')),
                    'transaction_reference' => $p->transaction_reference ?: '—',
                ];
            });

        // Material-side transactions (Purchase Invoices paid amount).
        $invoicePaymentRows = PurchaseInvoice::where('company_id', $companyId)
            ->whereIn('vendor_id', $supplierIds)
            ->where('paid_amount', '>', 0)
            ->with(['project'])
            ->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->get()
            ->map(function ($inv) {
                return [
                    'supplier_id' => (int) $inv->vendor_id,
                    'payment_date' => optional($inv->invoice_date)->format('Y-m-d'),
                    'payment_label' => 'Material Purchase',
                    'project_name' => $inv->project->name ?? 'N/A',
                    'amount' => (float) ($inv->paid_amount ?? 0),
                    'payment_method_label' => 'Invoice Payment',
                    'transaction_reference' => $inv->invoice_number ?: '—',
                ];
            });

        // Vehicle rent paid transactions.
        $vehicleRentPaymentRows = VehicleRent::where('company_id', $companyId)
            ->whereIn('supplier_id', $supplierIds)
            ->whereIn('payment_status', ['paid', 'partial'])
            ->where('paid_amount', '>', 0)
            ->with(['project'])
            ->orderByDesc('payment_date')
            ->orderByDesc('id')
            ->get()
            ->map(function ($rent) {
                $methodLabel = $rent->bank_account_id ? 'Bank Transfer' : 'Cash/Unspecified';
                return [
                    'supplier_id' => (int) $rent->supplier_id,
                    'payment_date' => optional($rent->payment_date ?: $rent->rent_date)->format('Y-m-d'),
                    'payment_label' => 'Vehicle Rent',
                    'project_name' => $rent->project->name ?? 'N/A',
                    'amount' => (float) ($rent->paid_amount ?? 0),
                    'payment_method_label' => $methodLabel,
                    'transaction_reference' => $rent->vehicle_number ?: '—',
                ];
            });

        // Construction materials paid transactions (legacy/material module).
        $supplierNameMap = Supplier::where('company_id', $companyId)
            ->whereIn('id', $supplierIds)
            ->pluck('id', 'name');

        $materialPaymentRows = ConstructionMaterial::where('company_id', $companyId)
            ->whereIn('supplier_name', $supplierNameMap->keys())
            ->whereIn('payment_status', ['Paid', 'Partial'])
            ->where('total_cost', '>', 0)
            ->orderByDesc('bill_date')
            ->orderByDesc('id')
            ->get()
            ->map(function ($m) use ($supplierNameMap) {
                $supplierId = (int) ($supplierNameMap[$m->supplier_name] ?? 0);
                if ($supplierId <= 0) {
                    return null;
                }
                return [
                    'supplier_id' => $supplierId,
                    'payment_date' => optional($m->bill_date ?: $m->delivery_date)->format('Y-m-d'),
                    'payment_label' => 'Construction Material',
                    'project_name' => $m->project_name ?: 'N/A',
                    'amount' => (float) ($m->total_cost ?? 0),
                    'payment_method_label' => $m->payment_mode ?: 'N/A',
                    'transaction_reference' => $m->bill_number ?: '—',
                ];
            })
            ->filter();

        $supplierPayments = $advancePaymentRows
            ->concat($invoicePaymentRows)
            ->concat($vehicleRentPaymentRows)
            ->concat($materialPaymentRows)
            ->groupBy('supplier_id')
            ->map(function ($group) {
                return $group->sortByDesc('payment_date')->values();
            });

        return view('admin.suppliers.index', compact('suppliers', 'supplierPayments', 'sortColumn', 'sortDir'));
    }

    public function create()
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.suppliers.index');
    }

    public function store(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name,NULL,id,company_id,' . $companyId,
            'contact' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'account_holder_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'branch_address' => 'nullable|string',
            'qr_code_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['company_id'] = $companyId;
        $data['is_active'] = $request->boolean('is_active', true);

        // Handle QR code image upload
        if ($request->hasFile('qr_code_image')) {
            $data['qr_code_image'] = $request->file('qr_code_image')->store('suppliers/qr-codes', 'public');
        }

        $supplier = Supplier::create($data);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Supplier created successfully.',
                'supplier' => [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'contact' => $supplier->contact,
                    'email' => $supplier->email,
                    'is_active' => $supplier->is_active,
                    'qr_code_image' => $supplier->qr_code_image ? Storage::url($supplier->qr_code_image) : null,
                ],
            ]);
        }

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        if ((int) $supplier->company_id !== (int) $companyId) {
            abort(404);
        }

        // Purchase invoices (materials / purchases)
        $purchaseInvoices = PurchaseInvoice::where('company_id', $companyId)
            ->where('vendor_id', $supplier->id)
            ->get(['id', 'total_amount', 'paid_amount', 'balance_amount']);

        $purchaseTotal = (float) $purchaseInvoices->sum('total_amount');
        $purchasePaid = (float) $purchaseInvoices->sum('paid_amount');
        $purchaseBalance = (float) $purchaseInvoices->sum('balance_amount');

        // Vehicle rents (handle ongoing daywise rents using calculated accessors)
        $vehicleRents = VehicleRent::where('company_id', $companyId)
            ->where('supplier_id', $supplier->id)
            ->get();

        $vehicleTotal = 0.0;
        $vehiclePaid = 0.0;
        $vehicleBalance = 0.0;

        foreach ($vehicleRents as $rent) {
            $rentTotal = $rent->is_ongoing ? (float) ($rent->calculated_total_amount ?? 0) : (float) ($rent->total_amount ?? 0);
            $rentBalance = $rent->is_ongoing ? (float) ($rent->calculated_balance_amount ?? 0) : (float) ($rent->balance_amount ?? 0);

            $vehicleTotal += $rentTotal;
            $vehiclePaid += (float) ($rent->paid_amount ?? 0);
            $vehicleBalance += $rentBalance;
        }

        // Advance payments to supplier (net off against balances)
        $advancePaymentsTotal = (float) AdvancePayment::where('company_id', $companyId)
            ->where('supplier_id', $supplier->id)
            ->sum('amount');

        $grossTotal = $purchaseTotal + $vehicleTotal;
        $grossPaid = $purchasePaid + $vehiclePaid;
        $grossBalance = $purchaseBalance + $vehicleBalance;
        $netBalance = $grossBalance - $advancePaymentsTotal;

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'supplier' => [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'contact' => $supplier->contact,
                    'email' => $supplier->email,
                    'address' => $supplier->address,
                    'bank_name' => $supplier->bank_name,
                    'account_holder_name' => $supplier->account_holder_name,
                    'account_number' => $supplier->account_number,
                    'branch_name' => $supplier->branch_name,
                    'branch_address' => $supplier->branch_address,
                    'is_active' => $supplier->is_active,
                    'qr_code_image' => $supplier->qr_code_image ? Storage::url($supplier->qr_code_image) : null,
                    'created_at' => $supplier->created_at->format('M d, Y H:i'),
                    'updated_at' => $supplier->updated_at->format('M d, Y H:i'),
                ],
                'financial' => [
                    'purchase_total' => $purchaseTotal,
                    'purchase_paid' => $purchasePaid,
                    'purchase_balance' => $purchaseBalance,
                    'vehicle_total' => $vehicleTotal,
                    'vehicle_paid' => $vehiclePaid,
                    'vehicle_balance' => $vehicleBalance,
                    'advance_payments_total' => $advancePaymentsTotal,
                    'gross_total' => $grossTotal,
                    'gross_paid' => $grossPaid,
                    'gross_balance' => $grossBalance,
                    'net_balance' => $netBalance,
                ],
            ]);
        }

        $financial = [
            'purchase_total' => $purchaseTotal,
            'purchase_paid' => $purchasePaid,
            'purchase_balance' => $purchaseBalance,
            'vehicle_total' => $vehicleTotal,
            'vehicle_paid' => $vehiclePaid,
            'vehicle_balance' => $vehicleBalance,
            'advance_payments_total' => $advancePaymentsTotal,
            'gross_total' => $grossTotal,
            'gross_paid' => $grossPaid,
            'gross_balance' => $grossBalance,
            'net_balance' => $netBalance,
        ];

        return view('admin.suppliers.show', compact('supplier', 'financial'));
    }

    public function edit(Supplier $supplier)
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'supplier' => [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'contact' => $supplier->contact,
                    'email' => $supplier->email,
                    'address' => $supplier->address,
                    'bank_name' => $supplier->bank_name,
                    'account_holder_name' => $supplier->account_holder_name,
                    'account_number' => $supplier->account_number,
                    'branch_name' => $supplier->branch_name,
                    'branch_address' => $supplier->branch_address,
                    'is_active' => $supplier->is_active,
                    'qr_code_image' => $supplier->qr_code_image ? Storage::url($supplier->qr_code_image) : null,
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.suppliers.index');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name,' . $supplier->id . ',id,company_id,' . $companyId,
            'contact' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'account_holder_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'branch_address' => 'nullable|string',
            'qr_code_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        // Handle QR code image upload
        if ($request->hasFile('qr_code_image')) {
            // Delete old QR code image if exists
            if ($supplier->qr_code_image && Storage::disk('public')->exists($supplier->qr_code_image)) {
                Storage::disk('public')->delete($supplier->qr_code_image);
            }
            $data['qr_code_image'] = $request->file('qr_code_image')->store('suppliers/qr-codes', 'public');
        }

        $supplier->update($data);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Supplier updated successfully.',
                'supplier' => [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'contact' => $supplier->contact,
                    'email' => $supplier->email,
                    'is_active' => $supplier->is_active,
                    'qr_code_image' => $supplier->qr_code_image ? Storage::url($supplier->qr_code_image) : null,
                ],
            ]);
        }

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Request $request, Supplier $supplier)
    {
        $supplier->delete();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Supplier deleted successfully.',
            ]);
        }

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}


