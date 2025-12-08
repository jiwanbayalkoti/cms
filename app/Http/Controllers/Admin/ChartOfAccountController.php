<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Support\CompanyContext;
use Illuminate\Http\Request;

class ChartOfAccountController extends Controller
{
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
        
        // Get accounts grouped by type
        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->orderBy('display_order')
            ->orderBy('account_code')
            ->get()
            ->groupBy('account_type');
        
        // Get parent accounts for dropdown
        $parentAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('level', '<=', 2)
            ->orderBy('account_code')
            ->get();
        
        return view('admin.chart_of_accounts.index', compact('accounts', 'parentAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $parentAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('level', '<=', 2)
            ->orderBy('account_code')
            ->get();
        
        return view('admin.chart_of_accounts.create', compact('parentAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $validated = $request->validate([
            'account_code' => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('chart_of_accounts')->where('company_id', $companyId)],
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'account_category' => 'nullable|string',
            'parent_account_id' => 'nullable|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
            'opening_balance' => 'nullable|numeric',
            'balance_type' => 'required|in:debit,credit',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer',
        ]);
        
        $parentAccount = $request->parent_account_id 
            ? ChartOfAccount::find($request->parent_account_id)
            : null;
        
        $validated['company_id'] = $companyId;
        $validated['level'] = $parentAccount ? ($parentAccount->level + 1) : 1;
        $validated['is_active'] = $request->has('is_active');
        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;
        $validated['display_order'] = $validated['display_order'] ?? 0;
        $validated['created_by'] = auth()->id();

        ChartOfAccount::create($validated);

        return redirect()->route('admin.chart-of-accounts.index')
            ->with('success', 'Chart of Account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->load(['parentAccount', 'childAccounts', 'journalEntryItems.account']);
        return view('admin.chart_of_accounts.show', compact('chartOfAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChartOfAccount $chartOfAccount)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $parentAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('id', '!=', $chartOfAccount->id)
            ->where('level', '<=', 2)
            ->orderBy('account_code')
            ->get();
        
        return view('admin.chart_of_accounts.edit', compact('chartOfAccount', 'parentAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $validated = $request->validate([
            'account_code' => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('chart_of_accounts')->where('company_id', $companyId)->ignore($chartOfAccount->id)],
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'account_category' => 'nullable|string',
            'parent_account_id' => 'nullable|exists:chart_of_accounts,id',
            'description' => 'nullable|string',
            'opening_balance' => 'nullable|numeric',
            'balance_type' => 'required|in:debit,credit',
            'is_active' => 'boolean',
            'display_order' => 'nullable|integer',
        ]);
        
        $parentAccount = $request->parent_account_id 
            ? ChartOfAccount::find($request->parent_account_id)
            : null;
        
        $validated['level'] = $parentAccount ? ($parentAccount->level + 1) : 1;
        $validated['is_active'] = $request->has('is_active');
        $validated['opening_balance'] = $validated['opening_balance'] ?? 0;
        $validated['display_order'] = $validated['display_order'] ?? 0;
        $validated['updated_by'] = auth()->id();
        
        // Prevent editing system accounts
        if ($chartOfAccount->is_system && ($validated['account_code'] !== $chartOfAccount->account_code || $validated['account_type'] !== $chartOfAccount->account_type)) {
            return back()->with('error', 'System accounts cannot be modified.');
        }

        $chartOfAccount->update($validated);

        return redirect()->route('admin.chart-of-accounts.index')
            ->with('success', 'Chart of Account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChartOfAccount $chartOfAccount)
    {
        // Prevent deleting system accounts
        if ($chartOfAccount->is_system) {
            return back()->with('error', 'System accounts cannot be deleted.');
        }
        
        // Prevent deleting accounts with child accounts
        if ($chartOfAccount->childAccounts()->count() > 0) {
            return back()->with('error', 'Cannot delete account with child accounts. Please delete child accounts first.');
        }
        
        // Prevent deleting accounts with journal entries
        if ($chartOfAccount->journalEntryItems()->count() > 0) {
            return back()->with('error', 'Cannot delete account with journal entries.');
        }

        $chartOfAccount->delete();

        return redirect()->route('admin.chart-of-accounts.index')
            ->with('success', 'Chart of Account deleted successfully.');
    }

    /**
     * Seed default chart of accounts for the company.
     */
    public function seedDefaults()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        // Check if accounts already exist
        if (ChartOfAccount::where('company_id', $companyId)->exists()) {
            return back()->with('error', 'Chart of Accounts already exists for this company.');
        }

        \Artisan::call('db:seed', ['--class' => 'ChartOfAccountSeeder']);

        return redirect()->route('admin.chart-of-accounts.index')
            ->with('success', 'Default Chart of Accounts seeded successfully.');
    }
}
