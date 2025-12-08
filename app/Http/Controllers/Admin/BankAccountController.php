<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\ChartOfAccount;
use App\Support\CompanyContext;
use Illuminate\Http\Request;

class BankAccountController extends Controller
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
        
        $bankAccounts = BankAccount::where('company_id', $companyId)
            ->with('chartOfAccount')
            ->orderBy('account_type')
            ->orderBy('account_name')
            ->get()
            ->groupBy('account_type');
        
        return view('admin.bank_accounts.index', compact('bankAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        // Get cash and bank accounts from chart of accounts
        $cashAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('account_type', 'asset')
            ->where(function($query) {
                $query->where('account_code', 'like', '1101%')
                      ->orWhere('account_name', 'like', '%cash%')
                      ->orWhere('account_name', 'like', '%bank%');
            })
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();
        
        return view('admin.bank_accounts.create', compact('cashAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'bank_name' => 'required|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'branch_address' => 'nullable|string',
            'swift_code' => 'nullable|string|max:20',
            'account_type' => 'required|in:bank,cash',
            'chart_of_account_id' => 'nullable|exists:chart_of_accounts,id',
            'opening_balance' => 'nullable|numeric',
            'opening_balance_date' => 'nullable|date',
            'currency' => 'required|string|max:3',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);
        
        $validated['company_id'] = $companyId;
        $validated['current_balance'] = $validated['opening_balance'] ?? 0;
        $validated['is_active'] = $request->has('is_active');
        $validated['created_by'] = auth()->id();
        
        // If no chart of account selected, create or find default
        if (empty($validated['chart_of_account_id'])) {
            if ($validated['account_type'] === 'cash') {
                $account = ChartOfAccount::where('company_id', $companyId)
                    ->where('account_code', '1101')
                    ->first();
            } else {
                $account = ChartOfAccount::where('company_id', $companyId)
                    ->where('account_code', '1102')
                    ->first();
            }
            $validated['chart_of_account_id'] = $account?->id;
        }

        BankAccount::create($validated);

        return redirect()->route('admin.bank-accounts.index')
            ->with('success', 'Bank/Cash account created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BankAccount $bankAccount)
    {
        $bankAccount->load(['chartOfAccount', 'company']);
        return view('admin.bank_accounts.show', compact('bankAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BankAccount $bankAccount)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $cashAccounts = ChartOfAccount::where('company_id', $companyId)
            ->where('account_type', 'asset')
            ->where(function($query) {
                $query->where('account_code', 'like', '1101%')
                      ->orWhere('account_code', 'like', '1102%')
                      ->orWhere('account_name', 'like', '%cash%')
                      ->orWhere('account_name', 'like', '%bank%');
            })
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();
        
        return view('admin.bank_accounts.edit', compact('bankAccount', 'cashAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'account_name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:100',
            'bank_name' => 'required|string|max:255',
            'branch_name' => 'nullable|string|max:255',
            'branch_address' => 'nullable|string',
            'swift_code' => 'nullable|string|max:20',
            'account_type' => 'required|in:bank,cash',
            'chart_of_account_id' => 'nullable|exists:chart_of_accounts,id',
            'opening_balance' => 'nullable|numeric',
            'opening_balance_date' => 'nullable|date',
            'currency' => 'required|string|max:3',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        $validated['updated_by'] = auth()->id();

        $bankAccount->update($validated);

        return redirect()->route('admin.bank-accounts.index')
            ->with('success', 'Bank/Cash account updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BankAccount $bankAccount)
    {
        // Check if account has transactions
        // This would need to check journal entries, invoices, etc.
        
        $bankAccount->delete();

        return redirect()->route('admin.bank-accounts.index')
            ->with('success', 'Bank/Cash account deleted successfully.');
    }

    /**
     * Show ledger for a bank account.
     */
    public function ledger(BankAccount $bankAccount, Request $request)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        
        if (!$bankAccount->chart_of_account_id) {
            return back()->with('error', 'Bank account is not linked to a chart of account.');
        }
        
        // Get transactions from journal entries
        $transactions = \App\Models\JournalEntryItem::where('account_id', $bankAccount->chart_of_account_id)
            ->whereHas('journalEntry', function($query) use ($bankAccount, $startDate, $endDate) {
                $query->where('company_id', $bankAccount->company_id)
                      ->where('is_posted', true)
                      ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->with(['journalEntry', 'account'])
            ->get()
            ->sortBy(function($item) {
                return $item->journalEntry->entry_date . ' ' . $item->journalEntry->entry_number;
            })
            ->values();
        
        $openingBalance = $bankAccount->opening_balance;
        $runningBalance = $openingBalance;
        
        foreach ($transactions as $transaction) {
            if ($transaction->entry_type === 'debit') {
                $runningBalance += $transaction->amount;
            } else {
                $runningBalance -= $transaction->amount;
            }
            $transaction->running_balance = $runningBalance;
        }
        
        return view('admin.bank_accounts.ledger', compact('bankAccount', 'transactions', 'openingBalance', 'runningBalance', 'startDate', 'endDate'));
    }
}
