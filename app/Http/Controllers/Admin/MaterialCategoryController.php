<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaterialCategory;
use Illuminate\Http\Request;

class MaterialCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $categories = MaterialCategory::orderBy('name')->paginate(15);

        return view('admin.material_categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.material_categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:material_categories,name',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        MaterialCategory::create($data);

        return redirect()->route('admin.material-categories.index')
            ->with('success', 'Material category created successfully.');
    }

    public function edit(MaterialCategory $material_category)
    {
        return view('admin.material_categories.edit', ['category' => $material_category]);
    }

    public function update(Request $request, MaterialCategory $material_category)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:material_categories,name,' . $material_category->id,
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $material_category->update($data);

        return redirect()->route('admin.material-categories.index')
            ->with('success', 'Material category updated successfully.');
    }

    public function destroy(MaterialCategory $material_category)
    {
        $material_category->delete();

        return redirect()->route('admin.material-categories.index')
            ->with('success', 'Material category deleted successfully.');
    }
}


