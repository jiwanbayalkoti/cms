<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreConstructionMaterialRequest;
use App\Http\Requests\Admin\UpdateConstructionMaterialRequest;
use App\Models\ConstructionMaterial;
use App\Models\MaterialCategory;
use App\Models\MaterialUnit;
use App\Models\MaterialName;
use App\Models\PaymentMode;
use App\Models\Project;
use App\Models\PurchasedBy;
use App\Models\Supplier;
use App\Models\WorkType;
use App\Models\AdvancePayment;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ConstructionMaterialsExport;

class ConstructionMaterialController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $query = ConstructionMaterial::where('company_id', $companyId);

        if ($request->filled('material_name')) {
            $query->where('material_name', 'like', '%' . $request->material_name . '%');
        }

        if ($request->filled('supplier_id')) {
            // Get supplier name by ID and filter materials by supplier_name
            $supplier = Supplier::where('company_id', $companyId)->find($request->supplier_id);
            if ($supplier) {
                $query->where('supplier_name', $supplier->name);
            }
        }

        if ($request->filled('project_name')) {
            $query->where('project_name', 'like', '%' . $request->project_name . '%');
        }

        if ($request->filled('purchased_by_id')) {
            $query->where('purchased_by_id', $request->purchased_by_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('delivery_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('delivery_date', '<=', $request->to_date);
        }

        // Get all filtered results for calculations (before pagination)
        $allMaterials = $query->get();
        
        // Calculate totals
        $totalCost = $allMaterials->sum('total_cost');
        $totalQuantityReceived = $allMaterials->sum('quantity_received');
        $totalQuantityUsed = $allMaterials->sum('quantity_used');
        $totalQuantityRemaining = $allMaterials->sum('quantity_remaining');
        
        // Calculate advance payments only when supplier filter is selected
        $totalAdvancePayments = 0;
        $netBalance = $totalCost;
        
        if ($request->filled('supplier_id')) {
            $totalAdvancePayments = AdvancePayment::where('company_id', $companyId)
                ->where('payment_type', 'material_payment')
                ->where('supplier_id', $request->supplier_id)
                ->sum('amount');
            
            // Calculate net balance (after advance payments)
            $netBalance = $totalCost - $totalAdvancePayments;
        }

        // Paginate results
        $materials = $query->latest()->paginate(15)->withQueryString();
        
        $materialNames = MaterialName::orderBy('name')->get();
        $suppliers = Supplier::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        $purchasedBies = PurchasedBy::where('is_active', true)->orderBy('name')->get();

        return view('admin.construction_materials.index', compact('materials', 'materialNames', 'suppliers', 'projects', 'purchasedBies', 'totalCost', 'totalQuantityReceived', 'totalQuantityUsed', 'totalQuantityRemaining', 'totalAdvancePayments', 'netBalance'));
    }

    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $categories = MaterialCategory::where('is_active', true)->orderBy('name')->get();
        $units = MaterialUnit::orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $workTypes = WorkType::where('is_active', true)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        $materialNames = MaterialName::orderBy('name')->get();
        $paymentModes = PaymentMode::orderBy('name')->get();
        $purchasedBies = PurchasedBy::where('is_active', true)->orderBy('name')->get();

        return view('admin.construction_materials.create', compact('categories', 'units', 'suppliers', 'workTypes', 'projects', 'materialNames', 'paymentModes', 'purchasedBies'));
    }

    public function store(StoreConstructionMaterialRequest $request)
    {
        $data = $request->validated();

        $quantityReceived = (float) ($data['quantity_received'] ?? 0);
        $ratePerUnit = (float) ($data['rate_per_unit'] ?? 0);
        $quantityUsed = (float) ($data['quantity_used'] ?? 0);

        $data['total_cost'] = $quantityReceived * $ratePerUnit;
        $data['quantity_remaining'] = max($quantityReceived - $quantityUsed, 0);

        if ($request->hasFile('bill_attachment')) {
            $data['bill_attachment'] = $request->file('bill_attachment')->store('materials/bills', 'public');
        }

        if ($request->hasFile('delivery_photo')) {
            $data['delivery_photo'] = $request->file('delivery_photo')->store('materials/photos', 'public');
        }

        ConstructionMaterial::create($data);

        return redirect()->route('admin.construction-materials.index')
            ->with('success', 'Construction material record created successfully.');
    }

    public function show(ConstructionMaterial $construction_material)
    {
        return view('admin.construction_materials.show', [
            'material' => $construction_material,
        ]);
    }

    public function edit(ConstructionMaterial $construction_material)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $categories = MaterialCategory::where('is_active', true)->orderBy('name')->get();
        $units = MaterialUnit::orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $workTypes = WorkType::where('is_active', true)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        $materialNames = MaterialName::orderBy('name')->get();
        $paymentModes = PaymentMode::orderBy('name')->get();
        $purchasedBies = PurchasedBy::where('is_active', true)->orderBy('name')->get();

        return view('admin.construction_materials.edit', [
            'material' => $construction_material,
            'categories' => $categories,
            'units' => $units,
            'suppliers' => $suppliers,
            'workTypes' => $workTypes,
            'projects' => $projects,
            'materialNames' => $materialNames,
            'paymentModes' => $paymentModes,
            'purchasedBies' => $purchasedBies,
        ]);
    }

    public function update(UpdateConstructionMaterialRequest $request, ConstructionMaterial $construction_material)
    {
        $data = $request->validated();

        $quantityReceived = (float) ($data['quantity_received'] ?? 0);
        $ratePerUnit = (float) ($data['rate_per_unit'] ?? 0);
        $quantityUsed = (float) ($data['quantity_used'] ?? 0);

        $data['total_cost'] = $quantityReceived * $ratePerUnit;
        $data['quantity_remaining'] = max($quantityReceived - $quantityUsed, 0);

        if ($request->hasFile('bill_attachment')) {
            if ($construction_material->bill_attachment) {
                Storage::disk('public')->delete($construction_material->bill_attachment);
            }
            $data['bill_attachment'] = $request->file('bill_attachment')->store('materials/bills', 'public');
        }

        if ($request->hasFile('delivery_photo')) {
            if ($construction_material->delivery_photo) {
                Storage::disk('public')->delete($construction_material->delivery_photo);
            }
            $data['delivery_photo'] = $request->file('delivery_photo')->store('materials/photos', 'public');
        }

        $construction_material->update($data);

        return redirect()->route('admin.construction-materials.index')
            ->with('success', 'Construction material record updated successfully.');
    }

    public function destroy(ConstructionMaterial $construction_material)
    {
        if ($construction_material->bill_attachment) {
            Storage::disk('public')->delete($construction_material->bill_attachment);
        }

        if ($construction_material->delivery_photo) {
            Storage::disk('public')->delete($construction_material->delivery_photo);
        }

        $construction_material->delete();

        return redirect()->route('admin.construction-materials.index')
            ->with('success', 'Construction material record deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = ConstructionMaterial::query();

        if ($request->filled('material_name')) {
            $query->where('material_name', 'like', '%' . $request->material_name . '%');
        }
        if ($request->filled('supplier_name')) {
            $query->where('supplier_name', 'like', '%' . $request->supplier_name . '%');
        }
        if ($request->filled('project_name')) {
            $query->where('project_name', 'like', '%' . $request->project_name . '%');
        }
        if ($request->filled('purchased_by_id')) {
            $query->where('purchased_by_id', $request->purchased_by_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('delivery_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('delivery_date', '<=', $request->to_date);
        }

        return Excel::download(new ConstructionMaterialsExport($query), 'construction_materials.xlsx');
    }
}


