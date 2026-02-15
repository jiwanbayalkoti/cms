<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Http\Controllers\Admin\Traits\HasProjectAccess;
use App\Models\Expense;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Staff;
use App\Models\Project;
use App\Models\ConstructionMaterial;
use App\Exports\ExpenseExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Support\CompanyContext;

class ExpenseController extends Controller
{
    use ValidatesForms, HasProjectAccess;
    /**
     * Create a new controller instance.
     */
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
        $query = Expense::with(['category', 'subcategory', 'staff', 'project', 'creator', 'updater', 'constructionMaterial', 'advancePayment', 'vehicleRent', 'expenseType'])
            ->where('company_id', $companyId);
        
        // Filter by accessible projects
        $this->filterByAccessibleProjects($query, 'project_id');
        
        // Filter by project
        if ($request->filled('project_id')) {
            $projectId = (int) $request->project_id;
            // Verify user has access to this project
            if (!$this->canAccessProject($projectId)) {
                abort(403, 'You do not have access to this project.');
            }
            $query->where('project_id', $projectId);
        }
        
        // Filter by expense type
        if ($request->filled('expense_type_id')) {
            $query->where('expense_type_id', $request->expense_type_id);
        }
        
        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by subcategory
        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }
        
        // Filter by keyword (item name, description, notes)
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('item_name', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%')
                    ->orWhere('notes', 'like', '%' . $keyword . '%');
            });
        }
        
        $expenses = $query->latest('date')->paginate(15)->withQueryString();
        
        // Get only accessible projects for dropdown
        $projects = $this->getAccessibleProjects();
        
        $expenseTypes = \App\Models\ExpenseType::orderBy('name')->get();
        $categories = Category::where('type', 'expense')->where('is_active', true)->orderBy('name')->get();
        
        // Get subcategories for the selected category if category filter is applied
        $subcategories = collect();
        if ($request->filled('category_id')) {
            $subcategories = Subcategory::where('category_id', $request->category_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();
        }
        
        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $expensesData = $expenses->map(function($expense) {
                $typeName = '';
                $typeClass = 'bg-gray-100 text-gray-800';
                
                if ($expense->constructionMaterial) {
                    $typeName = 'Purchase';
                    $typeClass = 'bg-blue-100 text-blue-800';
                } elseif ($expense->advancePayment) {
                    $typeName = 'Advance';
                    $typeClass = 'bg-yellow-100 text-yellow-800';
                } elseif ($expense->vehicleRent) {
                    $typeName = 'Vehicle rent';
                    $typeClass = 'bg-purple-100 text-purple-800';
                } elseif ($expense->expenseType) {
                    $typeName = $expense->expenseType->name;
                    $typeClass = 'bg-gray-100 text-gray-800';
                } else {
                    $typeName = 'N/A';
                }
                
                return [
                    'id' => $expense->id,
                    'date' => $expense->date->format('M d, Y'),
                    'type_name' => $typeName,
                    'type_class' => $typeClass,
                    'item_name' => $expense->item_name ?? ($expense->description ? Str::limit($expense->description, 30) : 'N/A'),
                    'project_name' => $expense->project ? $expense->project->name : 'N/A',
                    'category_name' => $expense->category->name,
                    'subcategory_name' => $expense->subcategory ? $expense->subcategory->name : null,
                    'staff_name' => $expense->staff ? $expense->staff->name : 'N/A',
                    'amount' => number_format($expense->amount, 2),
                    'has_construction_material' => $expense->constructionMaterial ? true : false,
                    'has_advance_payment' => $expense->advancePayment ? true : false,
                ];
            });
            
            return response()->json([
                'expenses' => $expensesData,
                'pagination' => $expenses->links()->render(),
                'current_page' => $expenses->currentPage(),
                'per_page' => $expenses->perPage(),
                'total_amount' => $expenses->sum('amount'),
            ]);
        }
        
        return view('admin.expenses.index', compact('expenses', 'projects', 'expenseTypes', 'categories', 'subcategories'));
    }

    /**
     * Export expenses to Excel.
     */
    public function exportExcel(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();

        $query = Expense::where('company_id', $companyId);

        $this->filterByAccessibleProjects($query, 'project_id');

        if ($request->filled('project_id')) {
            $projectId = (int) $request->project_id;
            if ($this->canAccessProject($projectId)) {
                $query->where('project_id', $projectId);
            }
        }
        if ($request->filled('expense_type_id')) {
            $query->where('expense_type_id', $request->expense_type_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }
        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('item_name', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%')
                    ->orWhere('notes', 'like', '%' . $keyword . '%');
            });
        }
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        $filename = 'expenses_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new ExpenseExport($query), $filename);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $categories = Category::where('type', 'expense')->where('is_active', true)->orderBy('name')->get();
        $subcategories = Subcategory::whereHas('category', function($q) {
            $q->where('type', 'expense')->where('is_active', true);
        })->where('is_active', true)->orderBy('name')->get();
        $staff = Staff::where('is_active', true)->orderBy('name')->get();
        // Get only accessible projects
        $projects = $this->getAccessibleProjects();
        $expenseTypes = \App\Models\ExpenseType::orderBy('name')->get();
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'categories' => $categories,
                'subcategories' => $subcategories,
                'staff' => $staff,
                'projects' => $projects,
                'expenseTypes' => $expenseTypes,
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.expenses.index');
    }

    /**
     * Validate expense form data (AJAX endpoint)
     */
    public function validateExpense(Request $request)
    {
        $rules = [
            'project_id' => 'nullable|exists:projects,id',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'expense_type_id' => 'required|exists:expense_types,id',
            'staff_id' => 'nullable|exists:staff,id',
            'item_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
        
        return $this->validateForm($request, $rules);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'expense_type_id' => 'required|exists:expense_types,id',
            'staff_id' => 'nullable|required_if:expense_type,salary,advance|exists:staff,id',
            'item_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max per image
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('expenses', 'public');
                $imagePaths[] = $path;
            }
            $validated['images'] = $imagePaths;
        }

        // Validate project access if project_id is provided
        if (!empty($validated['project_id'])) {
            $this->authorizeProjectAccess((int) $validated['project_id']);
        }

        $validated['company_id'] = CompanyContext::getActiveCompanyId();
        $validated['created_by'] = auth()->id();
        $expense = Expense::create($validated);
        $expense->load(['category', 'subcategory', 'staff', 'project', 'expenseType']);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $typeName = '';
            if ($expense->constructionMaterial) {
                $typeName = 'Purchase';
            } elseif ($expense->advancePayment) {
                $typeName = 'Advance';
            } elseif ($expense->vehicleRent) {
                $typeName = 'Vehicle rent';
            } elseif ($expense->expenseType) {
                $typeName = $expense->expenseType->name;
            } else {
                $typeName = 'N/A';
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Expense record created successfully.',
                'expense' => [
                    'id' => $expense->id,
                    'date' => $expense->date->format('M d, Y'),
                    'type_name' => $typeName,
                    'item_name' => $expense->item_name,
                    'description' => $expense->description,
                    'amount' => number_format($expense->amount, 2),
                    'project_name' => $expense->project ? $expense->project->name : 'N/A',
                    'category_name' => $expense->category->name,
                    'subcategory_name' => $expense->subcategory ? $expense->subcategory->name : null,
                    'staff_name' => $expense->staff ? $expense->staff->name : 'N/A',
                ],
            ]);
        }

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        if ($expense->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        
        // Check project access if expense has a project
        if ($expense->project_id) {
            $this->authorizeProjectAccess($expense->project_id);
        }
        
        $expense->load(['category', 'subcategory', 'staff', 'project', 'creator', 'updater', 'constructionMaterial', 'advancePayment', 'vehicleRent', 'expenseType']);
        
        // Return JSON only when AJAX and expects JSON (view modal); normal browser visit gets HTML
        if (request()->ajax() && request()->wantsJson()) {
            $typeName = '';
            if ($expense->constructionMaterial) {
                $typeName = 'Purchase';
            } elseif ($expense->advancePayment) {
                $typeName = 'Advance';
            } elseif ($expense->vehicleRent) {
                $typeName = 'Vehicle rent';
            } elseif ($expense->expenseType) {
                $typeName = $expense->expenseType->name;
            } else {
                $typeName = 'N/A';
            }
            
            $imageUrls = [];
            if ($expense->images) {
                foreach ($expense->images as $image) {
                    $imageUrls[] = Storage::url($image);
                }
            }
            
            return response()->json([
                'expense' => [
                    'id' => $expense->id,
                    'date' => $expense->date->format('Y-m-d'),
                    'formatted_date' => $expense->date->format('M d, Y'),
                    'type_name' => $typeName,
                    'item_name' => $expense->item_name,
                    'description' => $expense->description,
                    'amount' => number_format($expense->amount, 2),
                    'payment_method' => $expense->payment_method,
                    'notes' => $expense->notes,
                    'project_id' => $expense->project_id,
                    'project_name' => $expense->project ? $expense->project->name : 'N/A',
                    'category_id' => $expense->category_id,
                    'category_name' => $expense->category->name,
                    'subcategory_id' => $expense->subcategory_id,
                    'subcategory_name' => $expense->subcategory ? $expense->subcategory->name : null,
                    'staff_id' => $expense->staff_id,
                    'staff_name' => $expense->staff ? $expense->staff->name : 'N/A',
                    'expense_type_id' => $expense->expense_type_id,
                    'expense_type_name' => $expense->expenseType ? $expense->expenseType->name : 'N/A',
                    'images' => $imageUrls,
                    'has_construction_material' => $expense->constructionMaterial ? true : false,
                    'has_advance_payment' => $expense->advancePayment ? true : false,
                    'has_vehicle_rent' => $expense->vehicleRent ? true : false,
                    'created_by' => $expense->creator ? $expense->creator->name : 'N/A',
                    'updated_by' => $expense->updater ? $expense->updater->name : 'N/A',
                    'created_at' => $expense->created_at ? $expense->created_at->format('M d, Y H:i') : '',
                    'updated_at' => $expense->updated_at ? $expense->updated_at->format('M d, Y H:i') : '',
                ],
            ]);
        }

        // Materials that can be linked: no expense linked, or linked to this expense
        $materialsForLinkQuery = ConstructionMaterial::where('company_id', $expense->company_id)
            ->where(function ($q) use ($expense) {
                $q->whereDoesntHave('expense')
                    ->orWhereHas('expense', function ($q2) use ($expense) {
                        $q2->where('expenses.id', $expense->id);
                    });
            });
        $this->filterByAccessibleProjects($materialsForLinkQuery, 'project_id');
        $materialsForLink = $materialsForLinkQuery->orderByDesc('delivery_date')->orderByDesc('id')->get();

        return view('admin.expenses.show', compact('expense', 'materialsForLink'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        if ($expense->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $companyId = CompanyContext::getActiveCompanyId();
        $categories = Category::where('type', 'expense')->where('is_active', true)->orderBy('name')->get();
        $subcategories = Subcategory::whereHas('category', function($q) {
            $q->where('type', 'expense')->where('is_active', true);
        })->where('is_active', true)->orderBy('name')->get();
        // Check project access if expense has a project
        if ($expense->project_id) {
            $this->authorizeProjectAccess($expense->project_id);
        }
        
        $staff = Staff::where('is_active', true)->orderBy('name')->get();
        // Get only accessible projects
        $projects = $this->getAccessibleProjects();
        $expenseTypes = \App\Models\ExpenseType::orderBy('name')->get();
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            $imageUrls = [];
            if ($expense->images) {
                foreach ($expense->images as $image) {
                    $imageUrls[] = Storage::url($image);
                }
            }
            
            return response()->json([
                'expense' => [
                    'id' => $expense->id,
                    'date' => $expense->date->format('Y-m-d'),
                    'item_name' => $expense->item_name,
                    'description' => $expense->description,
                    'amount' => $expense->amount,
                    'payment_method' => $expense->payment_method,
                    'notes' => $expense->notes,
                    'project_id' => $expense->project_id,
                    'category_id' => $expense->category_id,
                    'subcategory_id' => $expense->subcategory_id,
                    'staff_id' => $expense->staff_id,
                    'expense_type_id' => $expense->expense_type_id,
                    'images' => $imageUrls,
                    'image_paths' => $expense->images ?? [],
                ],
                'categories' => $categories,
                'subcategories' => $subcategories,
                'staff' => $staff,
                'projects' => $projects,
                'expenseTypes' => $expenseTypes,
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.expenses.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        if ($expense->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'expense_type_id' => 'required|exists:expense_types,id',
            'staff_id' => 'nullable|required_if:expense_type,salary,advance|exists:staff,id',
            'item_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // 5MB max per image
            'delete_images' => 'nullable|array',
        ]);

        // Handle image uploads
        if ($request->hasFile('images')) {
            $imagePaths = $expense->images ?? [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('expenses', 'public');
                $imagePaths[] = $path;
            }
            $validated['images'] = $imagePaths;
        }

        // Handle image deletions
        if ($request->has('delete_images')) {
            $currentImages = $expense->images ?? [];
            foreach ($request->delete_images as $imageToDelete) {
                // Remove from array
                $currentImages = array_filter($currentImages, function($img) use ($imageToDelete) {
                    return $img !== $imageToDelete;
                });
                // Delete from storage
                if (Storage::disk('public')->exists($imageToDelete)) {
                    Storage::disk('public')->delete($imageToDelete);
                }
            }
            $validated['images'] = array_values($currentImages);
        }

        $validated['updated_by'] = auth()->id();
        $expense->update($validated);
        $expense->load(['category', 'subcategory', 'staff', 'project', 'expenseType', 'constructionMaterial', 'advancePayment', 'vehicleRent']);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $typeName = '';
            if ($expense->constructionMaterial) {
                $typeName = 'Purchase';
            } elseif ($expense->advancePayment) {
                $typeName = 'Advance';
            } elseif ($expense->vehicleRent) {
                $typeName = 'Vehicle rent';
            } elseif ($expense->expenseType) {
                $typeName = $expense->expenseType->name;
            } else {
                $typeName = 'N/A';
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Expense record updated successfully.',
                'expense' => [
                    'id' => $expense->id,
                    'date' => $expense->date->format('M d, Y'),
                    'type_name' => $typeName,
                    'item_name' => $expense->item_name,
                    'description' => $expense->description,
                    'amount' => number_format($expense->amount, 2),
                    'project_name' => $expense->project ? $expense->project->name : 'N/A',
                    'category_name' => $expense->category->name,
                    'subcategory_name' => $expense->subcategory ? $expense->subcategory->name : null,
                    'staff_name' => $expense->staff ? $expense->staff->name : 'N/A',
                ],
            ]);
        }

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        if ($expense->company_id !== auth()->user()->company_id) {
            abort(403);
        }
        // Delete associated images
        if ($expense->images) {
            foreach ($expense->images as $image) {
                if (Storage::disk('public')->exists($image)) {
                    Storage::disk('public')->delete($image);
                }
            }
        }

        $expense->delete();

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Expense record deleted successfully.',
            ]);
        }

        return redirect()->route('admin.expenses.index')
            ->with('success', 'Expense record deleted successfully.');
    }

    /**
     * Clone/Duplicate an expense record.
     */
    public function clone(Expense $expense)
    {
        if ($expense->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }

        // Create a new expense with the same data (excluding relationships and IDs)
        $newExpense = $expense->replicate();
        $newExpense->construction_material_id = null;
        $newExpense->advance_payment_id = null;
        $newExpense->vehicle_rent_id = null;
        $newExpense->created_by = auth()->id();
        $newExpense->updated_by = null;
        $newExpense->created_at = now();
        $newExpense->updated_at = now();
        
        // Copy images if they exist (create new file references)
        if ($expense->images && count($expense->images) > 0) {
            $newImagePaths = [];
            foreach ($expense->images as $imagePath) {
                if (Storage::disk('public')->exists($imagePath)) {
                    // Generate new filename
                    $pathInfo = pathinfo($imagePath);
                    $newFileName = $pathInfo['filename'] . '_copy_' . time() . '.' . ($pathInfo['extension'] ?? '');
                    $newPath = $pathInfo['dirname'] . '/' . $newFileName;
                    
                    // Copy the file
                    Storage::disk('public')->copy($imagePath, $newPath);
                    $newImagePaths[] = $newPath;
                }
            }
            $newExpense->images = $newImagePaths;
        }
        
        $newExpense->save();
        $newExpense->load(['category', 'subcategory', 'staff', 'project', 'expenseType', 'constructionMaterial', 'advancePayment', 'vehicleRent']);

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            $typeName = '';
            if ($newExpense->constructionMaterial) {
                $typeName = 'Purchase';
            } elseif ($newExpense->advancePayment) {
                $typeName = 'Advance';
            } elseif ($newExpense->vehicleRent) {
                $typeName = 'Vehicle rent';
            } elseif ($newExpense->expenseType) {
                $typeName = $newExpense->expenseType->name;
            } else {
                $typeName = 'N/A';
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Expense record duplicated successfully.',
                'expense' => [
                    'id' => $newExpense->id,
                    'date' => $newExpense->date->format('M d, Y'),
                    'type_name' => $typeName,
                    'item_name' => $newExpense->item_name,
                    'description' => $newExpense->description,
                    'amount' => number_format($newExpense->amount, 2),
                    'project_name' => $newExpense->project ? $newExpense->project->name : 'N/A',
                    'category_name' => $newExpense->category->name,
                    'subcategory_name' => $newExpense->subcategory ? $newExpense->subcategory->name : null,
                    'staff_name' => $newExpense->staff ? $newExpense->staff->name : 'N/A',
                ],
            ]);
        }

        return redirect()->route('admin.expenses.edit', $newExpense)
            ->with('success', 'Expense record duplicated successfully. You can now edit it.');
    }
}
