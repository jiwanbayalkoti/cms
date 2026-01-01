<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillCategory;
use App\Support\CompanyContext;
use Illuminate\Http\Request;

class BillCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        // CompanyScoped trait automatically filters by company_id
        $categories = BillCategory::orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.bill_categories.index', compact('categories'));
    }

    public function create()
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.bill-categories.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // CompanyScoped trait automatically sets company_id on create
        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $category = BillCategory::create($data);
        $category->loadCount('subcategories');

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill category created successfully.',
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'is_active' => $category->is_active,
                    'sort_order' => $category->sort_order,
                    'subcategories_count' => $category->subcategories_count,
                ],
            ]);
        }

        return redirect()->route('admin.bill-categories.index')
            ->with('success', 'Bill category created successfully.');
    }

    public function show(BillCategory $bill_category)
    {
        $bill_category->load('subcategories');
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'category' => [
                    'id' => $bill_category->id,
                    'name' => $bill_category->name,
                    'description' => $bill_category->description,
                    'is_active' => $bill_category->is_active,
                    'sort_order' => $bill_category->sort_order,
                    'created_at' => $bill_category->created_at->format('M d, Y H:i'),
                    'updated_at' => $bill_category->updated_at->format('M d, Y H:i'),
                    'subcategories' => $bill_category->subcategories->map(function($sub) {
                        return [
                            'id' => $sub->id,
                            'name' => $sub->name,
                            'description' => $sub->description,
                            'is_active' => $sub->is_active,
                        ];
                    }),
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.bill-categories.index');
    }

    public function edit(BillCategory $bill_category)
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'category' => [
                    'id' => $bill_category->id,
                    'name' => $bill_category->name,
                    'description' => $bill_category->description,
                    'is_active' => $bill_category->is_active,
                    'sort_order' => $bill_category->sort_order,
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.bill-categories.index');
    }

    public function update(Request $request, BillCategory $bill_category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $bill_category->update($data);
        $bill_category->loadCount('subcategories');

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill category updated successfully.',
                'category' => [
                    'id' => $bill_category->id,
                    'name' => $bill_category->name,
                    'description' => $bill_category->description,
                    'is_active' => $bill_category->is_active,
                    'sort_order' => $bill_category->sort_order,
                    'subcategories_count' => $bill_category->subcategories_count,
                ],
            ]);
        }

        return redirect()->route('admin.bill-categories.index')
            ->with('success', 'Bill category updated successfully.');
    }

    public function destroy(Request $request, BillCategory $bill_category)
    {
        $bill_category->delete();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bill category deleted successfully.',
            ]);
        }

        return redirect()->route('admin.bill-categories.index')
            ->with('success', 'Bill category deleted successfully.');
    }
}
