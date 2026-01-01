<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    use ValidatesForms;
    
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }
    
    /**
     * Validate category form data (AJAX endpoint)
     */
    public function validateCategory(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:income,expense',
            'is_active' => 'boolean',
        ];
        
        return $this->validateForm($request, $rules);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::with('subcategories')->latest()->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.categories.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:income,expense',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $category = Category::create($validated);
        $category->load('subcategories');

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'type' => $category->type,
                    'description' => $category->description,
                    'is_active' => $category->is_active,
                    'subcategories_count' => $category->subcategories->count(),
                ],
            ]);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $category->load('subcategories');
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'type' => $category->type,
                    'description' => $category->description,
                    'is_active' => $category->is_active,
                    'created_at' => $category->created_at->format('M d, Y H:i'),
                    'updated_at' => $category->updated_at->format('M d, Y H:i'),
                    'subcategories' => $category->subcategories->map(function($sub) {
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
        return redirect()->route('admin.categories.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'type' => $category->type,
                    'description' => $category->description,
                    'is_active' => $category->is_active,
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.categories.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:income,expense',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $category->update($validated);
        $category->load('subcategories');

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'type' => $category->type,
                    'description' => $category->description,
                    'is_active' => $category->is_active,
                    'subcategories_count' => $category->subcategories->count(),
                ],
            ]);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Category $category)
    {
        $category->delete();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.',
            ]);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Get subcategories for a given category (AJAX endpoint)
     */
    public function getSubcategories(Category $category)
    {
        $subcategories = $category->subcategories()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($subcategories);
    }
}
