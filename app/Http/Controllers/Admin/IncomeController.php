<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Income;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use App\Support\CompanyContext;

class IncomeController extends Controller
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
    public function index()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        $incomes = Income::with(['category', 'subcategory', 'creator', 'updater'])
            ->where('company_id', $companyId)
            ->latest('date')->paginate(15);
        return view('admin.incomes.index', compact('incomes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('type', 'income')->where('is_active', true)->orderBy('name')->get();
        $subcategories = Subcategory::whereHas('category', function($q) {
            $q->where('type', 'income')->where('is_active', true);
        })->where('is_active', true)->orderBy('name')->get();
        return view('admin.incomes.create', compact('categories', 'subcategories'));
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
        ]);
        $validated['company_id'] = CompanyContext::getActiveCompanyId();
        $validated['created_by'] = auth()->id();
        Income::create($validated);

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
        $income->load(['category', 'subcategory', 'creator', 'updater']);
        return view('admin.incomes.show', compact('income'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Income $income)
    {
        if ($income->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        $categories = Category::where('type', 'income')->where('is_active', true)->orderBy('name')->get();
        $subcategories = Subcategory::whereHas('category', function($q) {
            $q->where('type', 'income')->where('is_active', true);
        })->where('is_active', true)->orderBy('name')->get();
        return view('admin.incomes.edit', compact('income', 'categories', 'subcategories'));
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
            'category_id' => 'required|exists:categories,id',
            'subcategory_id' => 'nullable|exists:subcategories,id',
            'source' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();
        $income->update($validated);

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

        return redirect()->route('admin.incomes.index')
            ->with('success', 'Income record deleted successfully.');
    }
}
