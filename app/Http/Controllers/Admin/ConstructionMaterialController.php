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
use App\Models\Expense;
use App\Models\Category;
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

    /**
     * Get or create the "Materials" category for expenses.
     */
    private function getOrCreateMaterialsCategory($companyId)
    {
        $category = Category::where('company_id', $companyId)
            ->where('type', 'expense')
            ->where('name', 'Materials')
            ->first();

        if (!$category) {
            $category = Category::create([
                'company_id' => $companyId,
                'name' => 'Materials',
                'type' => 'expense',
                'description' => 'Construction materials and supplies',
                'is_active' => true,
            ]);
        }

        return $category;
    }

    /**
     * Create expense entry for material purchase when payment is made.
     */
    private function createExpenseFromMaterial(ConstructionMaterial $material)
    {
        // Only create expense if payment status is 'Paid' and no expense exists
        if ($material->payment_status === 'Paid' && !$material->expense) {
            $companyId = $material->company_id;
            $category = $this->getOrCreateMaterialsCategory($companyId);

            Expense::create([
                'company_id' => $companyId,
                'project_id' => $material->project_id,
                'construction_material_id' => $material->id,
                'category_id' => $category->id,
                'expense_type' => 'purchase',
                'item_name' => $material->material_name,
                'description' => "Material: {$material->material_name} ({$material->quantity_received} {$material->unit}) - Bill #{$material->bill_number}",
                'amount' => $material->total_cost,
                'date' => $material->bill_date ?? $material->delivery_date ?? now(),
                'payment_method' => $material->payment_mode,
                'notes' => "Supplier: {$material->supplier_name}" . ($material->supplier_contact ? " ({$material->supplier_contact})" : ''),
                'created_by' => auth()->id(),
            ]);
        }
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

        $material = ConstructionMaterial::create($data);

        // Auto-create expense entry if payment status is 'Paid'
        $this->createExpenseFromMaterial($material);

        return redirect()->route('admin.construction-materials.index')
            ->with('success', 'Construction material record created successfully.');
    }

    public function show(ConstructionMaterial $construction_material)
    {
        $construction_material->load('expense', 'project', 'purchasedBy');
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

        // Store old payment status to check if it changed
        $oldPaymentStatus = $construction_material->payment_status;

        $construction_material->update($data);

        // Refresh the model to get updated values
        $construction_material->refresh();

        // Auto-create expense entry if payment status changed to 'Paid'
        if ($oldPaymentStatus !== 'Paid' && $construction_material->payment_status === 'Paid') {
            $this->createExpenseFromMaterial($construction_material);
        }

        // Update existing expense if payment status changed and expense exists
        if ($construction_material->expense) {
            $expense = $construction_material->expense;
            $expense->update([
                'amount' => $construction_material->total_cost,
                'date' => $construction_material->bill_date ?? $construction_material->delivery_date ?? $expense->date,
                'payment_method' => $construction_material->payment_mode ?? $expense->payment_method,
                'item_name' => $construction_material->material_name,
                'description' => "Material: {$construction_material->material_name} ({$construction_material->quantity_received} {$construction_material->unit}) - Bill #{$construction_material->bill_number}",
                'notes' => "Supplier: {$construction_material->supplier_name}" . ($construction_material->supplier_contact ? " ({$construction_material->supplier_contact})" : ''),
                'updated_by' => auth()->id(),
            ]);
        }

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


