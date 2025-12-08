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
        return view('admin.bill_categories.create');
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

        BillCategory::create($data);

        return redirect()->route('admin.bill-categories.index')
            ->with('success', 'Bill category created successfully.');
    }

    public function show(BillCategory $bill_category)
    {
        $bill_category->load('subcategories');
        return view('admin.bill_categories.show', ['category' => $bill_category]);
    }

    public function edit(BillCategory $bill_category)
    {
        return view('admin.bill_categories.edit', ['category' => $bill_category]);
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

        return redirect()->route('admin.bill-categories.index')
            ->with('success', 'Bill category updated successfully.');
    }

    public function destroy(BillCategory $bill_category)
    {
        $bill_category->delete();

        return redirect()->route('admin.bill-categories.index')
            ->with('success', 'Bill category deleted successfully.');
    }
}
