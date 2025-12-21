<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Models\Supplier;
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
        $suppliers = Supplier::where('company_id', $companyId)
            ->orderBy('name')
            ->paginate(15);

        return view('admin.suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('admin.suppliers.create');
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

        Supplier::create($data);

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier created successfully.');
    }

    public function show(Supplier $supplier)
    {
        return view('admin.suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.suppliers.edit', compact('supplier'));
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

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('admin.suppliers.index')
            ->with('success', 'Supplier deleted successfully.');
    }
}


