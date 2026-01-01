<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\ValidatesForms;
use App\Http\Controllers\Admin\Traits\HasProjectAccess;
use App\Models\Income;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Support\CompanyContext;

class IncomeController extends Controller
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
     * Validate income form data (AJAX endpoint)
     */
    public function validateIncome(Request $request)
    {
        $rules = [
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'source' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
        ];
        
        return $this->validateForm($request, $rules);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $query = Income::with(['category', 'subcategory', 'project', 'creator', 'updater'])
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
        
        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by subcategory
        if ($request->filled('subcategory_id')) {
            $query->where('subcategory_id', $request->subcategory_id);
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }
        
        $incomes = $query->latest('date')->paginate(15)->withQueryString();
        
        // Get only accessible projects for dropdown
        $projects = $this->getAccessibleProjects();
        
        $categories = Category::where('type', 'income')->where('is_active', true)->orderBy('name')->get();
        
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
            $incomesData = $incomes->map(function($income) {
                return [
                    'id' => $income->id,
                    'date' => $income->date->format('M d, Y'),
                    'source' => $income->source,
                    'description' => $income->description,
                    'amount' => number_format($income->amount, 2),
                    'payment_method' => $income->payment_method ?? 'N/A',
                    'project_name' => $income->project ? $income->project->name : 'N/A',
                    'category_name' => $income->category->name,
                    'subcategory_name' => $income->subcategory ? $income->subcategory->name : null,
                ];
            });
            
            return response()->json([
                'incomes' => $incomesData,
                'pagination' => $incomes->links()->render(),
            ]);
        }
        
        return view('admin.incomes.index', compact('incomes', 'projects', 'categories', 'subcategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $categories = Category::where('type', 'income')->where('is_active', true)->orderBy('name')->get();
        $subcategories = Subcategory::whereHas('category', function($q) {
            $q->where('type', 'income')->where('is_active', true);
        })->where('is_active', true)->orderBy('name')->get();
        // Get only accessible projects
        $projects = $this->getAccessibleProjects();
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'categories' => $categories,
                'subcategories' => $subcategories,
                'projects' => $projects,
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.incomes.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'source' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
        ]);
        
        // Validate project access if project_id is provided
        if (!empty($validated['project_id'])) {
            $this->authorizeProjectAccess((int) $validated['project_id']);
        }
        
        $validated['company_id'] = CompanyContext::getActiveCompanyId();
        $validated['created_by'] = auth()->id();
        $income = Income::create($validated);
        $income->load(['category', 'subcategory', 'project']);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Income record created successfully.',
                'income' => [
                    'id' => $income->id,
                    'date' => $income->date->format('M d, Y'),
                    'source' => $income->source,
                    'description' => $income->description,
                    'amount' => number_format($income->amount, 2),
                    'payment_method' => $income->payment_method ?? 'N/A',
                    'project_name' => $income->project ? $income->project->name : 'N/A',
                    'category_name' => $income->category->name,
                    'subcategory_name' => $income->subcategory ? $income->subcategory->name : null,
                ],
            ]);
        }

        return redirect()->route('admin.incomes.index')
            ->with('success', 'Income record created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Income $income)
    {
        if ($income->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        
        // Check project access if income has a project
        if ($income->project_id) {
            $this->authorizeProjectAccess($income->project_id);
        }
        
        $income->load(['category', 'subcategory', 'project', 'creator', 'updater']);
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'income' => [
                    'id' => $income->id,
                    'date' => $income->date->format('Y-m-d'),
                    'formatted_date' => $income->date->format('M d, Y'),
                    'source' => $income->source,
                    'description' => $income->description,
                    'amount' => number_format($income->amount, 2),
                    'payment_method' => $income->payment_method,
                    'notes' => $income->notes,
                    'project_id' => $income->project_id,
                    'project_name' => $income->project ? $income->project->name : 'N/A',
                    'category_id' => $income->category_id,
                    'category_name' => $income->category->name,
                    'subcategory_id' => $income->subcategory_id,
                    'subcategory_name' => $income->subcategory ? $income->subcategory->name : null,
                    'created_by' => $income->creator ? $income->creator->name : 'N/A',
                    'updated_by' => $income->updater ? $income->updater->name : 'N/A',
                    'created_at' => $income->created_at ? $income->created_at->format('M d, Y H:i') : '',
                    'updated_at' => $income->updated_at ? $income->updated_at->format('M d, Y H:i') : '',
                ],
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.incomes.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Income $income)
    {
        if ($income->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $companyId = CompanyContext::getActiveCompanyId();
        $categories = Category::where('type', 'income')->where('is_active', true)->orderBy('name')->get();
        $subcategories = Subcategory::whereHas('category', function($q) {
            $q->where('type', 'income')->where('is_active', true);
        })->where('is_active', true)->orderBy('name')->get();
        // Check project access if income has a project
        if ($income->project_id) {
            $this->authorizeProjectAccess($income->project_id);
        }
        
        // Get only accessible projects
        $projects = $this->getAccessibleProjects();
        
        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'income' => [
                    'id' => $income->id,
                    'date' => $income->date->format('Y-m-d'),
                    'source' => $income->source,
                    'description' => $income->description,
                    'amount' => $income->amount,
                    'payment_method' => $income->payment_method,
                    'notes' => $income->notes,
                    'project_id' => $income->project_id,
                    'category_id' => $income->category_id,
                    'subcategory_id' => $income->subcategory_id,
                ],
                'categories' => $categories,
                'subcategories' => $subcategories,
                'projects' => $projects,
            ]);
        }
        
        // Redirect to index page since popup handles everything
        return redirect()->route('admin.incomes.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Income $income)
    {
        if ($income->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'source' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Validate project access if project_id is being changed
        if (!empty($validated['project_id']) && $validated['project_id'] != $income->project_id) {
            $this->authorizeProjectAccess((int) $validated['project_id']);
        }
        
        // Check project access for existing income
        if ($income->project_id) {
            $this->authorizeProjectAccess($income->project_id);
        }

        $validated['updated_by'] = auth()->id();
        $income->update($validated);
        $income->load(['category', 'subcategory', 'project']);

        // Return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Income record updated successfully.',
                'income' => [
                    'id' => $income->id,
                    'date' => $income->date->format('M d, Y'),
                    'source' => $income->source,
                    'description' => $income->description,
                    'amount' => number_format($income->amount, 2),
                    'payment_method' => $income->payment_method ?? 'N/A',
                    'project_name' => $income->project ? $income->project->name : 'N/A',
                    'category_name' => $income->category->name,
                    'subcategory_name' => $income->subcategory ? $income->subcategory->name : null,
                ],
            ]);
        }

        return redirect()->route('admin.incomes.index')
            ->with('success', 'Income record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Income $income)
    {
        if ($income->company_id !== auth()->user()->company_id) {
            abort(403);
        }
        $income->delete();

        // Return JSON for AJAX requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Income record deleted successfully.',
            ]);
        }

        return redirect()->route('admin.incomes.index')
            ->with('success', 'Income record deleted successfully.');
    }
}
