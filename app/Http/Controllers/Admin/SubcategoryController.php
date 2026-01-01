<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Models\Subcategory;
use App\Models\Category;
use Illuminate\Http\Request;

class SubcategoryController extends Controller
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
     * Validate subcategory form data (AJAX endpoint)
     */
    public function validateSubcategory(Request $request)
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ];
        
        return $this->validateForm($request, $rules);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $subcategories = Subcategory::with('category')->latest()->paginate(10);
        return view('admin.subcategories.index', compact('subcategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'categories' => $categories,
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.subcategories.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $subcategory = Subcategory::create($validated);
        $subcategory->load('category');

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subcategory created successfully.',
                'subcategory' => [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'description' => $subcategory->description,
                    'is_active' => $subcategory->is_active,
                    'category_name' => $subcategory->category->name,
                    'category_id' => $subcategory->category_id,
                ],
            ]);
        }

        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Subcategory created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Subcategory $subcategory)
    {
        $subcategory->load('category');
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'subcategory' => [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'description' => $subcategory->description,
                    'is_active' => $subcategory->is_active,
                    'category_name' => $subcategory->category->name,
                    'category_id' => $subcategory->category_id,
                    'created_at' => $subcategory->created_at->format('M d, Y H:i'),
                    'updated_at' => $subcategory->updated_at->format('M d, Y H:i'),
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.subcategories.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subcategory $subcategory)
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'subcategory' => [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'description' => $subcategory->description,
                    'is_active' => $subcategory->is_active,
                    'category_id' => $subcategory->category_id,
                ],
                'categories' => $categories,
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.subcategories.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subcategory $subcategory)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $subcategory->update($validated);
        $subcategory->load('category');

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subcategory updated successfully.',
                'subcategory' => [
                    'id' => $subcategory->id,
                    'name' => $subcategory->name,
                    'description' => $subcategory->description,
                    'is_active' => $subcategory->is_active,
                    'category_name' => $subcategory->category->name,
                    'category_id' => $subcategory->category_id,
                ],
            ]);
        }

        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Subcategory updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Subcategory $subcategory)
    {
        $subcategory->delete();

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Subcategory deleted successfully.',
            ]);
        }

        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Subcategory deleted successfully.');
    }
}
