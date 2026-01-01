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
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.material-categories.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:material_categories,name',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $category = MaterialCategory::create($data);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Material category created successfully.',
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'is_active' => $category->is_active,
                ],
            ]);
        }

        return redirect()->route('admin.material-categories.index')
            ->with('success', 'Material category created successfully.');
    }

    public function edit(MaterialCategory $material_category)
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'category' => [
                    'id' => $material_category->id,
                    'name' => $material_category->name,
                    'description' => $material_category->description,
                    'is_active' => $material_category->is_active,
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.material-categories.index');
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

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Material category updated successfully.',
                'category' => [
                    'id' => $material_category->id,
                    'name' => $material_category->name,
                    'description' => $material_category->description,
                    'is_active' => $material_category->is_active,
                ],
            ]);
        }

        return redirect()->route('admin.material-categories.index')
            ->with('success', 'Material category updated successfully.');
    }

    public function destroy(Request $request, MaterialCategory $material_category)
    {
        $material_category->delete();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Material category deleted successfully.',
            ]);
        }

        return redirect()->route('admin.material-categories.index')
            ->with('success', 'Material category deleted successfully.');
    }
}


