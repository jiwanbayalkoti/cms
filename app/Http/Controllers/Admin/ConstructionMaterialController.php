<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Http\Controllers\Admin\Traits\HasProjectAccess;
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
    use ValidatesForms, HasProjectAccess;
    
    public function __construct()
    {
        $this->middleware('admin');
    }
    
    /**
     * Validate construction material form data (AJAX endpoint)
     */
    public function validateMaterial(Request $request)
    {
        $rules = [
            'material_name' => 'required|string|max:255',
            'material_category' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'quantity_received' => 'required|numeric|min:0',
            'rate_per_unit' => 'required|numeric|min:0',
            'quantity_used' => 'nullable|numeric|min:0',
            'wastage_quantity' => 'nullable|numeric|min:0',
            'supplier_name' => 'nullable|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
            'bill_number' => 'nullable|string|max:255',
            'bill_date' => 'nullable|date',
            'payment_status' => 'required|in:Paid,Unpaid,Partial',
            'payment_mode' => 'nullable|string|max:255',
            'purchased_by_id' => 'nullable|exists:purchased_bies,id',
            'delivery_date' => 'nullable|date',
            'delivery_site' => 'nullable|string|max:255',
            'delivered_by' => 'nullable|string|max:255',
            'received_by' => 'nullable|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'work_type' => 'nullable|string|max:255',
            'usage_purpose' => 'nullable|string',
            'status' => 'required|in:Received,Pending,Returned,Damaged',
            'approved_by' => 'nullable|string|max:255',
            'approval_date' => 'nullable|date',
            'bill_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'delivery_photo' => 'nullable|file|mimes:jpg,jpeg,png|max:4096',
        ];
        
        return $this->validateForm($request, $rules);
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
        
        // Filter by accessible projects
        $this->filterByAccessibleProjects($query, 'project_id');

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
        // Get only accessible projects
        $projects = $this->getAccessibleProjects();
        $purchasedBies = PurchasedBy::where('is_active', true)->orderBy('name')->get();

        // Return JSON for AJAX requests
        if ($request->ajax()) {
            $netBalanceValue = $netBalance ?? $totalCost;
            $tableHtml = view('admin.construction_materials.partials.table', [
                'materials' => $materials,
                'totalCost' => $totalCost,
                'totalAdvancePayments' => $totalAdvancePayments ?? 0,
                'netBalance' => $netBalanceValue
            ])->render();
            $paginationHtml = view('admin.construction_materials.partials.pagination', compact('materials'))->render();
            $summaryHtml = view('admin.construction_materials.partials.summary', [
                'totalCost' => $totalCost,
                'totalAdvancePayments' => $totalAdvancePayments ?? 0,
                'netBalance' => $netBalanceValue
            ])->render();
            
            return response()->json([
                'html' => $tableHtml,
                'pagination' => $paginationHtml,
                'summary' => $summaryHtml,
                'materials' => $materials,
                'totalCost' => $totalCost,
                'totalAdvancePayments' => $totalAdvancePayments ?? 0,
                'netBalance' => $netBalanceValue
            ]);
        }

        return view('admin.construction_materials.index', compact('materials', 'materialNames', 'suppliers', 'projects', 'purchasedBies', 'totalCost', 'totalQuantityReceived', 'totalQuantityUsed', 'totalQuantityRemaining', 'totalAdvancePayments', 'netBalance'));
    }

    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $categories = MaterialCategory::where('is_active', true)->orderBy('name')->get();
        $units = MaterialUnit::orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        // WorkType model removed - work_type is now a simple text field
        // Get only accessible projects
        $projects = $this->getAccessibleProjects();
        $materialNames = MaterialName::orderBy('name')->get();
        $paymentModes = PaymentMode::orderBy('name')->get();
        $purchasedBies = PurchasedBy::where('is_active', true)->orderBy('name')->get();

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'categories' => $categories,
                'units' => $units,
                'suppliers' => $suppliers,
                'projects' => $projects,
                'materialNames' => $materialNames,
                'paymentModes' => $paymentModes,
                'purchasedBies' => $purchasedBies,
            ]);
        }

        // Redirect to index page since popup handles everything
        return redirect()->route('admin.construction-materials.index');
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

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $material->load('project');
            return response()->json([
                'success' => true,
                'message' => 'Construction material record created successfully.',
                'material' => [
                    'id' => $material->id,
                    'material_name' => $material->material_name,
                    'material_category' => $material->material_category,
                    'project_name' => $material->project ? $material->project->name : ($material->project_name ?? ''),
                    'supplier_name' => $material->supplier_name,
                    'quantity_received' => $material->quantity_received,
                    'quantity_used' => $material->quantity_used,
                    'quantity_remaining' => $material->quantity_remaining,
                    'unit' => $material->unit,
                    'total_cost' => $material->total_cost,
                    'status' => $material->status,
                ],
            ]);
        }

        return redirect()->route('admin.construction-materials.index')
            ->with('success', 'Construction material record created successfully.');
    }

    public function show(ConstructionMaterial $construction_material)
    {
        $construction_material->load('expense', 'project', 'purchasedBy');
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'material' => [
                    'id' => $construction_material->id,
                    'material_name' => $construction_material->material_name,
                    'material_category' => $construction_material->material_category,
                    'unit' => $construction_material->unit,
                    'project_name' => $construction_material->project ? $construction_material->project->name : ($construction_material->project_name ?? ''),
                    'work_type' => $construction_material->work_type,
                    'delivery_site' => $construction_material->delivery_site,
                    'delivered_by' => $construction_material->delivered_by,
                    'received_by' => $construction_material->received_by,
                    'quantity_received' => $construction_material->quantity_received,
                    'quantity_used' => $construction_material->quantity_used,
                    'quantity_remaining' => $construction_material->quantity_remaining,
                    'wastage_quantity' => $construction_material->wastage_quantity,
                    'status' => $construction_material->status,
                    'delivery_date' => $construction_material->delivery_date ? $construction_material->delivery_date->format('Y-m-d') : null,
                    'approved_by' => $construction_material->approved_by,
                    'approval_date' => $construction_material->approval_date ? $construction_material->approval_date->format('Y-m-d') : null,
                    'usage_purpose' => $construction_material->usage_purpose,
                    'rate_per_unit' => $construction_material->rate_per_unit,
                    'total_cost' => $construction_material->total_cost,
                    'bill_number' => $construction_material->bill_number,
                    'bill_date' => $construction_material->bill_date ? $construction_material->bill_date->format('Y-m-d') : null,
                    'payment_status' => $construction_material->payment_status,
                    'payment_mode' => $construction_material->payment_mode,
                    'supplier_name' => $construction_material->supplier_name,
                    'supplier_contact' => $construction_material->supplier_contact,
                    'bill_attachment' => $construction_material->bill_attachment,
                    'delivery_photo' => $construction_material->delivery_photo,
                    'expense' => $construction_material->expense ? [
                        'id' => $construction_material->expense->id,
                        'created_at' => $construction_material->expense->created_at->format('Y-m-d H:i'),
                    ] : null,
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.construction-materials.index');
    }

    public function edit(ConstructionMaterial $construction_material)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $categories = MaterialCategory::where('is_active', true)->orderBy('name')->get();
        $units = MaterialUnit::orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        // WorkType model removed - work_type is now a simple text field
        // Get only accessible projects
        $projects = $this->getAccessibleProjects();
        $materialNames = MaterialName::orderBy('name')->get();
        $paymentModes = PaymentMode::orderBy('name')->get();
        $purchasedBies = PurchasedBy::where('is_active', true)->orderBy('name')->get();

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'material' => $construction_material,
                'categories' => $categories,
                'units' => $units,
                'suppliers' => $suppliers,
                'projects' => $projects,
                'materialNames' => $materialNames,
                'paymentModes' => $paymentModes,
                'purchasedBies' => $purchasedBies,
            ]);
        }

        // Redirect to index page since popup handles everything
        return redirect()->route('admin.construction-materials.index');
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

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $construction_material->load('project');
            return response()->json([
                'success' => true,
                'message' => 'Construction material record updated successfully.',
                'material' => [
                    'id' => $construction_material->id,
                    'material_name' => $construction_material->material_name,
                    'material_category' => $construction_material->material_category,
                    'project_name' => $construction_material->project ? $construction_material->project->name : ($construction_material->project_name ?? ''),
                    'supplier_name' => $construction_material->supplier_name,
                    'quantity_received' => $construction_material->quantity_received,
                    'quantity_used' => $construction_material->quantity_used,
                    'quantity_remaining' => $construction_material->quantity_remaining,
                    'unit' => $construction_material->unit,
                    'total_cost' => $construction_material->total_cost,
                    'status' => $construction_material->status,
                ],
            ]);
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

    public function destroy(Request $request, ConstructionMaterial $construction_material)
    {
        if ($construction_material->bill_attachment) {
            Storage::disk('public')->delete($construction_material->bill_attachment);
        }

        if ($construction_material->delivery_photo) {
            Storage::disk('public')->delete($construction_material->delivery_photo);
        }

        $construction_material->delete();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Construction material record deleted successfully.',
            ]);
        }

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

    /**
     * Clone/Duplicate a construction material record.
     */
    public function clone(ConstructionMaterial $construction_material)
    {
        if ($construction_material->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }

        // Create a new material with the same data (excluding expense relationship)
        $newMaterial = $construction_material->replicate();
        $newMaterial->created_at = now();
        $newMaterial->updated_at = now();
        
        // Copy bill attachment if it exists
        if ($construction_material->bill_attachment && Storage::disk('public')->exists($construction_material->bill_attachment)) {
            $pathInfo = pathinfo($construction_material->bill_attachment);
            $newFileName = $pathInfo['filename'] . '_copy_' . time() . '.' . ($pathInfo['extension'] ?? '');
            $newPath = $pathInfo['dirname'] . '/' . $newFileName;
            Storage::disk('public')->copy($construction_material->bill_attachment, $newPath);
            $newMaterial->bill_attachment = $newPath;
        }
        
        // Copy delivery photo if it exists
        if ($construction_material->delivery_photo && Storage::disk('public')->exists($construction_material->delivery_photo)) {
            $pathInfo = pathinfo($construction_material->delivery_photo);
            $newFileName = $pathInfo['filename'] . '_copy_' . time() . '.' . ($pathInfo['extension'] ?? '');
            $newPath = $pathInfo['dirname'] . '/' . $newFileName;
            Storage::disk('public')->copy($construction_material->delivery_photo, $newPath);
            $newMaterial->delivery_photo = $newPath;
        }
        
        // Reset payment status to Unpaid for the clone
        $newMaterial->payment_status = 'Unpaid';
        
        $newMaterial->save();

        return redirect()->route('admin.construction-materials.edit', $newMaterial)
            ->with('success', 'Construction material record duplicated successfully. You can now edit it.');
    }
}


