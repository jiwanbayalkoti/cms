<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillCategory;
use App\Models\BillSubcategory;
use App\Support\CompanyContext;
use Illuminate\Http\Request;

class BillSubcategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        // CompanyScoped trait automatically filters by company_id
        $query = BillSubcategory::with('category');

        if ($request->filled('category_id')) {
            $query->where('bill_category_id', $request->category_id);
        }

        $subcategories = $query->orderBy('sort_order')->orderBy('name')->paginate(15)->withQueryString();
        // CompanyScoped trait automatically filters by company_id
        $categories = BillCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            $subcategoriesData = $subcategories->map(function ($subcategory) {
                return [
                    'id' => $subcategory->id,
                    'category_name' => $subcategory->category->name ?? '—',
                    'name' => $subcategory->name,
                    'description' => $subcategory->description ?? '—',
                    'sort_order' => $subcategory->sort_order,
                    'is_active' => $subcategory->is_active,
                ];
            });
            
            return response()->json([
                'subcategories' => $subcategoriesData,
                'pagination' => view('components.pagination', [
                    'paginator' => $subcategories,
                    'wrapperClass' => 'mt-3',
                    'showInfo' => false
                ])->render(),
            ]);
        }

        return view('admin.bill_subcategories.index', compact('subcategories', 'categories'));
    }

    public function create(Request $request)
    {
        // CompanyScoped trait automatically filters by company_id
        $categories = BillCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedCategoryId = $request->get('bill_category_id');

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'categories' => $categories,
                'selectedCategoryId' => $selectedCategoryId,
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.bill-subcategories.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'bill_category_id' => 'required|exists:bill_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // CompanyScoped trait automatically sets company_id on create
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $subcategory = BillSubcategory::create($data);
        $subcategory->load('category');

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill subcategory created successfully.',
                'subcategory' => [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'description' => $subcategory->description,
                    'is_active' => $subcategory->is_active,
                    'sort_order' => $subcategory->sort_order,
                    'category_name' => $subcategory->category->name,
                    'bill_category_id' => $subcategory->bill_category_id,
                ],
            ]);
        }

        return redirect()->route('admin.bill-subcategories.index')
            ->with('success', 'Bill subcategory created successfully.');
    }

    public function show(BillSubcategory $bill_subcategory)
    {
        $bill_subcategory->load('category');
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'subcategory' => [
                    'id' => $bill_subcategory->id,
                    'name' => $bill_subcategory->name,
                    'description' => $bill_subcategory->description,
                    'is_active' => $bill_subcategory->is_active,
                    'sort_order' => $bill_subcategory->sort_order,
                    'category_name' => $bill_subcategory->category->name,
                    'bill_category_id' => $bill_subcategory->bill_category_id,
                    'created_at' => $bill_subcategory->created_at->format('M d, Y H:i'),
                    'updated_at' => $bill_subcategory->updated_at->format('M d, Y H:i'),
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.bill-subcategories.index');
    }

    public function edit(BillSubcategory $bill_subcategory)
    {
        // CompanyScoped trait automatically filters by company_id
        $categories = BillCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'subcategory' => [
                    'id' => $bill_subcategory->id,
                    'name' => $bill_subcategory->name,
                    'description' => $bill_subcategory->description,
                    'is_active' => $bill_subcategory->is_active,
                    'sort_order' => $bill_subcategory->sort_order,
                    'bill_category_id' => $bill_subcategory->bill_category_id,
                ],
                'categories' => $categories,
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.bill-subcategories.index');
    }

    public function update(Request $request, BillSubcategory $bill_subcategory)
    {
        $data = $request->validate([
            'bill_category_id' => 'required|exists:bill_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $bill_subcategory->update($data);
        $bill_subcategory->load('category');

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill subcategory updated successfully.',
                'subcategory' => [
                    'id' => $bill_subcategory->id,
                    'name' => $bill_subcategory->name,
                    'description' => $bill_subcategory->description,
                    'is_active' => $bill_subcategory->is_active,
                    'sort_order' => $bill_subcategory->sort_order,
                    'category_name' => $bill_subcategory->category->name,
                    'bill_category_id' => $bill_subcategory->bill_category_id,
                ],
            ]);
        }

        return redirect()->route('admin.bill-subcategories.index')
            ->with('success', 'Bill subcategory updated successfully.');
    }

    public function destroy(Request $request, BillSubcategory $bill_subcategory)
    {
        $bill_subcategory->delete();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill subcategory deleted successfully.',
            ]);
        }

        return redirect()->route('admin.bill-subcategories.index')
            ->with('success', 'Bill subcategory deleted successfully.');
    }
}
