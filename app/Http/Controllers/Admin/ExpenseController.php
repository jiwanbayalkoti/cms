<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Staff;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Support\CompanyContext;

class ExpenseController extends Controller
{
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
        
        // Filter by project
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
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
        
        $expenses = $query->latest('date')->paginate(15)->withQueryString();
        
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        
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
        
        return view('admin.expenses.index', compact('expenses', 'projects', 'expenseTypes', 'categories', 'subcategories'));
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
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        $expenseTypes = \App\Models\ExpenseType::orderBy('name')->get();
        return view('admin.expenses.create', compact('categories', 'subcategories', 'staff', 'projects', 'expenseTypes'));
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

        $validated['company_id'] = CompanyContext::getActiveCompanyId();
        $validated['created_by'] = auth()->id();
        Expense::create($validated);

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
        $expense->load(['category', 'subcategory', 'staff', 'project', 'creator', 'updater', 'constructionMaterial', 'advancePayment']);
        return view('admin.expenses.show', compact('expense'));
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
        $staff = Staff::where('is_active', true)->orderBy('name')->get();
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        $expenseTypes = \App\Models\ExpenseType::orderBy('name')->get();
        return view('admin.expenses.edit', compact('expense', 'categories', 'subcategories', 'staff', 'projects', 'expenseTypes'));
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

        return redirect()->route('admin.expenses.edit', $newExpense)
            ->with('success', 'Expense record duplicated successfully. You can now edit it.');
    }
}
