<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\HasProjectAccess;
use App\Models\BankAccount;
use App\Models\Loan;
use App\Models\LoanPayment;
use App\Models\Staff;
use App\Models\Supplier;
use App\Models\Project;
use App\Support\CompanyContext;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    use HasProjectAccess;

    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();

        // Base query (NO pagination/order here) - used for totals to avoid ONLY_FULL_GROUP_BY errors
        $query = Loan::with(['project', 'supplier', 'staff', 'bankAccount', 'payments', 'payments.bankAccount'])
            ->where('company_id', $companyId);

        $this->filterByAccessibleProjects($query, 'project_id');

        if ($request->filled('project_id')) {
            $projectId = (int) $request->project_id;
            if (!$this->canAccessProject($projectId)) {
                abort(403, 'You do not have access to this project.');
            }
            $query->where('project_id', $projectId);
        }

        if ($request->filled('start_date')) {
            $query->where('loan_date', '>=', $request->start_date);
        }
        // Default end_date = today (till date)
        $endDate = $request->get('end_date') ?: date('Y-m-d');
        $query->whereDate('loan_date', '<=', $endDate);

        // If start_date not given, start from earliest loan date (till endDate)
        $startDate = $request->get('start_date');
        if (!$startDate) {
            $startDate = (string) ((clone $query)->min('loan_date') ?: $endDate);
        }
        $query->whereDate('loan_date', '>=', $startDate);

        if ($request->filled('direction')) {
            $query->where('direction', $request->direction);
        }

        $listQuery = (clone $query);
        [$sortColumn, $sortDir] = $this->applyLoanListSorting($listQuery, $request);
        $loans = $listQuery->paginate(15)->withQueryString();

        $projects = $this->getAccessibleProjects();
        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $staff = Staff::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $bankAccounts = BankAccount::where('company_id', $companyId)->where('is_active', true)->orderBy('account_name')->get();

        // Interest is annual (%). Calculate interest from loan_date till endDate (today by default).
        $endCarbon = Carbon::parse($endDate)->endOfDay();
        $allForTotals = (clone $query)->get(['direction', 'amount', 'interest_rate', 'loan_date']);

        $totalReceived = 0.0;
        $totalRepaid = 0.0;

        foreach ($allForTotals as $l) {
            $principal = (float) ($l->amount ?? 0);
            $rate = (float) ($l->interest_rate ?? 0);
            $loanDate = $l->loan_date ? Carbon::parse($l->loan_date)->startOfDay() : null;
            $days = $loanDate ? max(0, $loanDate->diffInDays($endCarbon)) : 0;

            // Received: payable = principal + accrued interest till date
            $accruedInterest = $principal * $rate / 100 * ($days / 365);
            $payable = $principal + $accruedInterest;

            if ($l->direction === 'repaid') {
                // Repaid is money out; treat as actual paid amount (no extra accrual)
                $totalRepaid += $principal;
            } else {
                $totalReceived += $payable;
            }
        }

        // Pay button payments (loan_payments) — money paid back against taken loans
        if (!$request->filled('direction') || $request->direction === 'received') {
            $paymentsQuery = LoanPayment::query()
                ->where('company_id', $companyId)
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->whereHas('loan', function ($q) {
                    $q->where('direction', 'received');
                });
            $this->filterByAccessibleProjects($paymentsQuery, 'project_id');
            if ($request->filled('project_id')) {
                $paymentsQuery->where('project_id', (int) $request->project_id);
            }
            $totalRepaid += (float) $paymentsQuery->sum('amount');
        }

        $netBalance = $totalReceived - $totalRepaid;

        return view('admin.loans.index', compact(
            'loans',
            'projects',
            'suppliers',
            'staff',
            'bankAccounts',
            'totalReceived',
            'totalRepaid',
            'netBalance',
            'startDate',
            'endDate',
            'sortColumn',
            'sortDir'
        ));
    }

    /**
     * Whitelist sorting for loan list (table: loans). Uses sort_dir (asc|desc) to avoid
     * clashing with the filter param "direction" (received/repaid).
     *
     * @return array{0: string, 1: string} [sortColumn, sortDir]
     */
    private function applyLoanListSorting(Builder $query, Request $request): array
    {
        $sortColumn = $request->get('sort', 'loan_date');
        $sortDir = strtolower((string) $request->get('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['loan_date', 'direction', 'party', 'project', 'amount', 'interest_rate', 'payment_method', 'bank'];
        if (! in_array($sortColumn, $allowedSorts, true)) {
            $sortColumn = 'loan_date';
        }

        $dirSql = $sortDir === 'asc' ? 'ASC' : 'DESC';

        switch ($sortColumn) {
            case 'project':
                $query->orderByRaw(
                    '(SELECT p.name FROM projects AS p WHERE p.id = loans.project_id LIMIT 1) '.$dirSql
                );
                break;
            case 'party':
                $query->orderByRaw(
                    'COALESCE(loans.party_source, loans.party_name, loans.source, '
                    .'(SELECT s.name FROM suppliers AS s WHERE s.id = loans.supplier_id LIMIT 1), '
                    .'(SELECT st.name FROM staff AS st WHERE st.id = loans.staff_id LIMIT 1), '
                    ."''"
                    .') '.$dirSql
                );
                break;
            case 'bank':
                $query->orderByRaw(
                    '(SELECT b.account_name FROM bank_accounts AS b WHERE b.id = loans.bank_account_id LIMIT 1) '.$dirSql
                );
                break;
            case 'direction':
                $query->orderBy('loans.direction', $sortDir);
                break;
            default:
                $query->orderBy('loans.'.$sortColumn, $sortDir);
                break;
        }

        $query->orderBy('loans.id', $sortDir === 'asc' ? 'asc' : 'desc');

        return [$sortColumn, $sortDir];
    }

    public function create()
    {
        $companyId = CompanyContext::getActiveCompanyId();

        $projects = $this->getAccessibleProjects();
        $suppliers = Supplier::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $staff = Staff::where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $bankAccounts = BankAccount::where('company_id', $companyId)->where('is_active', true)->orderBy('account_name')->get();

        return view('admin.loans.create', compact('projects', 'suppliers', 'staff', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        $companyId = CompanyContext::getActiveCompanyId();

        $validated = $request->validate([
            'direction' => 'required|in:received,repaid',
            'project_id' => 'nullable|exists:projects,id',
            'party_source' => 'nullable|string|max:255',
            // Backward compatibility (old fields)
            'party_name' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'interest_rate' => 'nullable|numeric|min:0|max:1000',
            'loan_date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'notes' => 'nullable|string',
        ]);

        if (!empty($validated['project_id'])) {
            $this->authorizeProjectAccess((int) $validated['project_id']);
        }

        // Merge party + source into one field for easier reporting.
        $partySource = $validated['party_source'] ?? null;
        if ($partySource !== null && $partySource !== '') {
            $validated['party_name'] = $partySource;
            $validated['source'] = $partySource;
            $validated['party_source'] = $partySource;
        } else {
            // If user filled legacy fields, mirror them into party_source.
            $validated['party_source'] = $validated['party_name'] ?? $validated['source'] ?? null;
        }

        // Supplier/Staff removed from loan form workflow.
        $validated['supplier_id'] = null;
        $validated['staff_id'] = null;

        $validated['company_id'] = $companyId;
        $validated['created_by'] = auth()->id();

        $loan = Loan::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Loan transaction created successfully.',
                'loan_id' => $loan->id,
            ]);
        }

        return redirect()->route('admin.loans.index')->with('success', 'Loan transaction created successfully.');
    }

    public function edit(Loan $loan)
    {
        if ($loan->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }

        $projects = $this->getAccessibleProjects();
        $suppliers = Supplier::where('company_id', $loan->company_id)->where('is_active', true)->orderBy('name')->get();
        $staff = Staff::where('company_id', $loan->company_id)->where('is_active', true)->orderBy('name')->get();
        $bankAccounts = BankAccount::where('company_id', $loan->company_id)->where('is_active', true)->orderBy('account_name')->get();

        return view('admin.loans.edit', compact('loan', 'projects', 'suppliers', 'staff', 'bankAccounts'));
    }

    public function update(Request $request, Loan $loan)
    {
        if ($loan->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }

        $validated = $request->validate([
            'direction' => 'required|in:received,repaid',
            'project_id' => 'nullable|exists:projects,id',
            'party_source' => 'nullable|string|max:255',
            // Backward compatibility (old fields)
            'party_name' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string|max:100',
            'amount' => 'required|numeric|min:0.01',
            'interest_rate' => 'nullable|numeric|min:0|max:1000',
            'loan_date' => 'required|date',
            'payment_method' => 'nullable|string|max:255',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'notes' => 'nullable|string',
        ]);

        if (!empty($validated['project_id'])) {
            $this->authorizeProjectAccess((int) $validated['project_id']);
        }

        // Merge party + source into one field for easier reporting.
        $partySource = $validated['party_source'] ?? null;
        if ($partySource !== null && $partySource !== '') {
            $validated['party_name'] = $partySource;
            $validated['source'] = $partySource;
            $validated['party_source'] = $partySource;
        } else {
            // If user filled legacy fields, mirror them into party_source.
            $validated['party_source'] = $validated['party_name'] ?? $validated['source'] ?? null;
        }

        $validated['supplier_id'] = null;
        $validated['staff_id'] = null;

        $validated['updated_by'] = auth()->id();

        $loan->update($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Loan transaction updated successfully.',
                'loan_id' => $loan->id,
            ]);
        }

        return redirect()->route('admin.loans.index')->with('success', 'Loan transaction updated successfully.');
    }

    public function destroy(Loan $loan)
    {
        if ($loan->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }

        $loan->delete();

        return redirect()->route('admin.loans.index')->with('success', 'Loan transaction deleted successfully.');
    }

    public function outstanding(Request $request, Loan $loan)
    {
        if ($loan->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }

        $asOf = $request->filled('as_of') ? Carbon::parse($request->as_of) : now();
        $loan->load('payments');
        $out = $loan->outstandingAsOf($asOf);

        return response()->json([
            'loan_id' => $loan->id,
            'as_of' => $asOf->format('Y-m-d'),
            'principal_outstanding' => (float) $out['principal_outstanding'],
            'interest_due' => (float) $out['interest_due'],
            'total_due' => (float) $out['total_due'],
            'is_closed' => (bool) $out['is_closed'],
        ]);
    }

    public function recordPayment(Request $request, Loan $loan)
    {
        if ($loan->company_id !== CompanyContext::getActiveCompanyId()) {
            abort(403);
        }
        if ($loan->direction !== 'received') {
            return back()->with('error', 'Payments can only be recorded for received loans.');
        }

        $validated = $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'nullable|string|max:255',
            'bank_account_id' => 'nullable|exists:bank_accounts,id',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $payDate = Carbon::parse($validated['payment_date'])->startOfDay();
        $minDate = $loan->loan_date ? Carbon::parse($loan->loan_date)->startOfDay() : null;
        if ($minDate && $payDate->lt($minDate)) {
            return back()->withErrors(['payment_date' => 'Payment date cannot be before loan date.']);
        }

        // Enforce non-decreasing payment dates for correct interest calculation
        $lastPaymentDate = LoanPayment::where('loan_id', $loan->id)->max('payment_date');
        if ($lastPaymentDate && $payDate->lt(Carbon::parse($lastPaymentDate)->startOfDay())) {
            return back()->withErrors(['payment_date' => 'Payment date must be on/after last payment date (' . $lastPaymentDate . ').']);
        }

        DB::beginTransaction();
        try {
            $loan->load('payments');
            $outBefore = $loan->outstandingAsOf($payDate);

            $amount = (float) $validated['amount'];
            $interestPay = min((float) $outBefore['interest_due'], $amount);
            $amountAfterInterest = $amount - $interestPay;
            $principalPay = min((float) $outBefore['principal_outstanding'], $amountAfterInterest);

            $payment = LoanPayment::create([
                'company_id' => $loan->company_id,
                'project_id' => $loan->project_id,
                'loan_id' => $loan->id,
                'payment_date' => $payDate->format('Y-m-d'),
                'amount' => $validated['amount'],
                'interest_paid' => $interestPay,
                'principal_paid' => $principalPay,
                'payment_method' => $validated['payment_method'] ?? null,
                'bank_account_id' => $validated['bank_account_id'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Recalc outstanding after payment (as of same day)
            $loan->refresh()->load('payments');
            $outAfter = $loan->outstandingAsOf($payDate);
            if ($outAfter['is_closed']) {
                $loan->update([
                    'is_closed' => true,
                    'closed_at' => $payDate->format('Y-m-d'),
                ]);
            }

            DB::commit();
            return redirect()->route('admin.loans.index')->with('success', 'Payment recorded successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to record payment: ' . $e->getMessage());
        }
    }
}

