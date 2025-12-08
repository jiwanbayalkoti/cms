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

        $subcategories = $query->orderBy('sort_order')->orderBy('name')->paginate(15);
        // CompanyScoped trait automatically filters by company_id
        $categories = BillCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.bill_subcategories.index', compact('subcategories', 'categories'));
    }

    public function create(Request $request)
    {
        // CompanyScoped trait automatically filters by company_id
        $categories = BillCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        $selectedCategoryId = $request->get('bill_category_id');

        return view('admin.bill_subcategories.create', compact('categories', 'selectedCategoryId'));
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

        BillSubcategory::create($data);

        return redirect()->route('admin.bill-subcategories.index')
            ->with('success', 'Bill subcategory created successfully.');
    }

    public function show(BillSubcategory $bill_subcategory)
    {
        $bill_subcategory->load('category');
        return view('admin.bill_subcategories.show', ['subcategory' => $bill_subcategory]);
    }

    public function edit(BillSubcategory $bill_subcategory)
    {
        // CompanyScoped trait automatically filters by company_id
        $categories = BillCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.bill_subcategories.edit', [
            'subcategory' => $bill_subcategory,
            'categories' => $categories,
        ]);
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

        return redirect()->route('admin.bill-subcategories.index')
            ->with('success', 'Bill subcategory updated successfully.');
    }

    public function destroy(BillSubcategory $bill_subcategory)
    {
        $bill_subcategory->delete();

        return redirect()->route('admin.bill-subcategories.index')
            ->with('success', 'Bill subcategory deleted successfully.');
    }
}
