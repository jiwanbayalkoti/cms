<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\JournalEntryItem;
use App\Models\ChartOfAccount;
use App\Models\Project;
use App\Support\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $query = JournalEntry::where('company_id', $companyId)
            ->with(['items.account', 'project', 'creator'])
            ->orderBy('entry_date', 'desc')
            ->orderBy('entry_number', 'desc');
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('entry_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('entry_date', '<=', $request->end_date);
        }
        
        // Filter by posted status
        if ($request->filled('is_posted')) {
            $query->where('is_posted', $request->is_posted === '1');
        }
        
        $journalEntries = $query->paginate(20);
        
        return view('admin.journal_entries.index', compact('journalEntries'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();
        
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        
        return view('admin.journal_entries.create', compact('accounts', 'projects'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();
        
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'description' => 'nullable|string',
            'reference' => 'nullable|string',
            'entry_type' => 'required|in:manual,purchase,sales,payment,receipt,adjustment,closing',
            'project_id' => 'nullable|exists:projects,id',
            'items' => 'required|array|min:2',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
            'items.*.entry_type' => 'required|in:debit,credit',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.description' => 'nullable|string',
        ]);
        
        // Validate that debits equal credits
        $totalDebit = 0;
        $totalCredit = 0;
        
        foreach ($validated['items'] as $item) {
            if ($item['entry_type'] === 'debit') {
                $totalDebit += $item['amount'];
            } else {
                $totalCredit += $item['amount'];
            }
        }
        
        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withInput()->withErrors([
                'items' => 'Total debits must equal total credits. Debit: ' . number_format($totalDebit, 2) . ', Credit: ' . number_format($totalCredit, 2)
            ]);
        }
        
        DB::beginTransaction();
        try {
            $journalEntry = JournalEntry::create([
                'company_id' => $companyId,
                'entry_number' => JournalEntry::generateEntryNumber(),
                'entry_date' => $validated['entry_date'],
                'description' => $validated['description'],
                'reference' => $validated['reference'],
                'entry_type' => $validated['entry_type'],
                'project_id' => $validated['project_id'] ?? null,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'is_posted' => false,
                'created_by' => auth()->id(),
            ]);
            
            foreach ($validated['items'] as $index => $item) {
                JournalEntryItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $item['account_id'],
                    'entry_type' => $item['entry_type'],
                    'amount' => $item['amount'],
                    'description' => $item['description'] ?? null,
                    'line_number' => $index + 1,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('admin.journal-entries.show', $journalEntry)
                ->with('success', 'Journal entry created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create journal entry: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(JournalEntry $journalEntry)
    {
        $journalEntry->load(['items.account', 'project', 'creator', 'postedBy']);
        return view('admin.journal_entries.show', compact('journalEntry'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(JournalEntry $journalEntry)
    {
        if ($journalEntry->is_posted) {
            return redirect()->route('admin.journal-entries.show', $journalEntry)
                ->with('error', 'Posted journal entries cannot be edited.');
        }
        
        $companyId = CompanyContext::getActiveCompanyId();
        
        $accounts = ChartOfAccount::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('account_code')
            ->get();
        
        $projects = Project::where('company_id', $companyId)
            ->where('status', '!=', 'cancelled')
            ->orderBy('name')
            ->get();
        
        $journalEntry->load('items');
        
        return view('admin.journal_entries.edit', compact('journalEntry', 'accounts', 'projects'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, JournalEntry $journalEntry)
    {
        if ($journalEntry->is_posted) {
            return back()->with('error', 'Posted journal entries cannot be edited.');
        }
        
        $validated = $request->validate([
            'entry_date' => 'required|date',
            'description' => 'nullable|string',
            'reference' => 'nullable|string',
            'entry_type' => 'required|in:manual,purchase,sales,payment,receipt,adjustment,closing',
            'project_id' => 'nullable|exists:projects,id',
            'items' => 'required|array|min:2',
            'items.*.account_id' => 'required|exists:chart_of_accounts,id',
            'items.*.entry_type' => 'required|in:debit,credit',
            'items.*.amount' => 'required|numeric|min:0.01',
            'items.*.description' => 'nullable|string',
        ]);
        
        // Validate that debits equal credits
        $totalDebit = 0;
        $totalCredit = 0;
        
        foreach ($validated['items'] as $item) {
            if ($item['entry_type'] === 'debit') {
                $totalDebit += $item['amount'];
            } else {
                $totalCredit += $item['amount'];
            }
        }
        
        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withInput()->withErrors([
                'items' => 'Total debits must equal total credits.'
            ]);
        }
        
        DB::beginTransaction();
        try {
            $journalEntry->update([
                'entry_date' => $validated['entry_date'],
                'description' => $validated['description'],
                'reference' => $validated['reference'],
                'entry_type' => $validated['entry_type'],
                'project_id' => $validated['project_id'] ?? null,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'updated_by' => auth()->id(),
            ]);
            
            // Delete existing items
            $journalEntry->items()->delete();
            
            // Create new items
            foreach ($validated['items'] as $index => $item) {
                JournalEntryItem::create([
                    'journal_entry_id' => $journalEntry->id,
                    'account_id' => $item['account_id'],
                    'entry_type' => $item['entry_type'],
                    'amount' => $item['amount'],
                    'description' => $item['description'] ?? null,
                    'line_number' => $index + 1,
                ]);
            }
            
            DB::commit();
            
            return redirect()->route('admin.journal-entries.show', $journalEntry)
                ->with('success', 'Journal entry updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update journal entry: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(JournalEntry $journalEntry)
    {
        if ($journalEntry->is_posted) {
            return back()->with('error', 'Posted journal entries cannot be deleted.');
        }
        
        $journalEntry->items()->delete();
        $journalEntry->delete();
        
        return redirect()->route('admin.journal-entries.index')
            ->with('success', 'Journal entry deleted successfully.');
    }

    /**
     * Post a journal entry (make it final).
     */
    public function post(JournalEntry $journalEntry)
    {
        if ($journalEntry->is_posted) {
            return back()->with('error', 'Journal entry is already posted.');
        }
        
        if (!$journalEntry->isBalanced()) {
            return back()->with('error', 'Journal entry is not balanced. Debits must equal credits.');
        }
        
        $journalEntry->update([
            'is_posted' => true,
            'posted_at' => now(),
            'posted_by' => auth()->id(),
        ]);
        
        return back()->with('success', 'Journal entry posted successfully.');
    }

    /**
     * Unpost a journal entry (reverse posting).
     */
    public function unpost(JournalEntry $journalEntry)
    {
        if (!$journalEntry->is_posted) {
            return back()->with('error', 'Journal entry is not posted.');
        }
        
        $journalEntry->update([
            'is_posted' => false,
            'posted_at' => null,
            'posted_by' => null,
        ]);
        
        return back()->with('success', 'Journal entry unposted successfully.');
    }
}
