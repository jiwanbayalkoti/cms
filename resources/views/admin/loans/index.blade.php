@extends('admin.layout')

@section('title', 'Loans')

@section('content')
<div class="mb-4 flex justify-between items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Loans</h1>
        <p class="mt-1 text-gray-600 text-sm">Record loan received/repaid separately from Income.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.reports.loans') }}" class="btn btn-outline-primary btn-sm btn-keep-text">
            <i class="bi bi-file-earmark-text"></i>
            <span>Loans Report</span>
        </a>
        <button type="button" class="btn btn-success btn-sm btn-keep-text" onclick="openLoanCrudModal('create', null, event)">
            <i class="bi bi-plus-circle"></i>
            <span>Add Loan</span>
        </button>
    </div>
</div>

<div id="loanSummaryCards" class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-4">
    <div class="bg-white border rounded-lg p-3">
        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 mb-2">Taken loans (company borrowed)</div>
        <div class="grid grid-cols-3 gap-2">
            <div class="rounded-md bg-green-50 border border-green-100 px-2.5 py-2">
                <div class="text-xs text-muted leading-tight">Total Taken</div>
                <div class="text-lg font-bold text-green-700 leading-tight mt-0.5">${{ number_format($totalTaken ?? $totalReceived ?? 0, 2) }}</div>
            </div>
            <div class="rounded-md bg-amber-50 border border-amber-100 px-2.5 py-2">
                <div class="text-xs text-muted leading-tight">Total Repaid</div>
                <div class="text-lg font-bold text-amber-700 leading-tight mt-0.5">${{ number_format($totalRepaid ?? 0, 2) }}</div>
            </div>
            <div class="rounded-md bg-slate-50 border border-slate-100 px-2.5 py-2">
                <div class="text-xs text-muted leading-tight">Remaining</div>
                <div class="text-lg font-bold {{ ($takenRemaining ?? $netBalance ?? 0) >= 0 ? 'text-slate-800' : 'text-red-600' }} leading-tight mt-0.5">${{ number_format($takenRemaining ?? $netBalance ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="bg-white border rounded-lg p-3">
        <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 mb-2">Given loans (company lent)</div>
        <div class="grid grid-cols-3 gap-2">
            <div class="rounded-md bg-red-50 border border-red-100 px-2.5 py-2">
                <div class="text-xs text-muted leading-tight">Total Given</div>
                <div class="text-lg font-bold text-red-700 leading-tight mt-0.5">${{ number_format($totalGiven ?? 0, 2) }}</div>
            </div>
            <div class="rounded-md bg-blue-50 border border-blue-100 px-2.5 py-2">
                <div class="text-xs text-muted leading-tight">Returned / Repaid</div>
                <div class="text-lg font-bold text-blue-700 leading-tight mt-0.5">${{ number_format($totalGivenReturned ?? 0, 2) }}</div>
            </div>
            <div class="rounded-md bg-slate-50 border border-slate-100 px-2.5 py-2">
                <div class="text-xs text-muted leading-tight">Remaining</div>
                <div class="text-lg font-bold {{ ($givenRemaining ?? 0) >= 0 ? 'text-slate-800' : 'text-red-600' }} leading-tight mt-0.5">${{ number_format($givenRemaining ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="bg-white shadow-lg rounded-lg p-3 mb-4">
    <form method="GET" action="{{ route('admin.loans.index') }}" class="row g-2" id="loanFilterForm" onsubmit="applyLoanFilters(event)">
        <input type="hidden" name="sort" id="loan_filter_sort" value="{{ $sortColumn ?? request('sort', 'loan_date') }}">
        <input type="hidden" name="sort_dir" id="loan_filter_sort_dir" value="{{ $sortDir ?? request('sort_dir', 'desc') }}">
        <div class="col-md-3">
            <label class="form-label small mb-1">Project</label>
            <select name="project_id" class="form-select form-select-sm" onchange="applyLoanFilters(null, true)">
                <option value="">All Projects</option>
                @foreach($projects ?? [] as $p)
                    <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Start Date</label>
            <input type="date" name="start_date" value="{{ request('start_date', $startDate ?? '') }}" class="form-control form-control-sm" onchange="applyLoanFilters(null, true)">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">End Date</label>
            <input type="date" name="end_date" value="{{ request('end_date', $endDate ?? '') }}" class="form-control form-control-sm" onchange="applyLoanFilters(null, true)">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Direction</label>
            <select name="direction" class="form-select form-select-sm" onchange="applyLoanFilters(null, true)">
                <option value="">All</option>
                <option value="received" {{ request('direction') === 'received' ? 'selected' : '' }}>Taken</option>
                <option value="repaid" {{ request('direction') === 'repaid' ? 'selected' : '' }}>Given</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-1">Per Page</label>
            <select name="per_page" class="form-select form-select-sm" onchange="applyLoanFilters(null, true)">
                <option value="15" {{ (string) request('per_page', '15') === '15' ? 'selected' : '' }}>15</option>
                <option value="25" {{ (string) request('per_page') === '25' ? 'selected' : '' }}>25</option>
                <option value="50" {{ (string) request('per_page') === '50' ? 'selected' : '' }}>50</option>
                <option value="all" {{ strtolower((string) request('per_page')) === 'all' ? 'selected' : '' }}>All</option>
            </select>
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="button" onclick="resetLoanFilters()" class="btn btn-outline-secondary btn-sm btn-keep-text w-100">
                <i class="bi bi-arrow-counterclockwise"></i>
                <span>Reset</span>
            </button>
        </div>
    </form>
</div>

<div id="loanTableSection" class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="px-4 sm:px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
        <div>
            <h2 class="text-lg font-semibold text-gray-900">Loan Transactions</h2>
            <p class="text-xs text-gray-500 mt-0.5">Click a row to view payment / return history</p>
        </div>
        <div class="text-xs text-gray-500">
            {{ $loans->total() }} record{{ $loans->total() === 1 ? '' : 's' }}
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50" id="loan-thead">
                @php
                    $sc = $sortColumn ?? request('sort', 'loan_date');
                    $sd = $sortDir ?? request('sort_dir', 'desc');
                    $loanThSort = function (string $col) use ($sc, $sd) {
                        $active = $sc === $col;
                        $icon = $active
                            ? ($sd === 'asc' ? 'bi-sort-up' : 'bi-sort-down')
                            : 'bi-arrow-down-up';
                        $cls = $active ? 'text-indigo-600' : 'text-gray-400';
                        return '<i class="bi '.$icon.' ms-1 '.$cls.'" aria-hidden="true"></i>';
                    };
                @endphp
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button type="button" data-sort-col="loan_date" onclick="sortLoans('loan_date')" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-gray-500 hover:text-indigo-700 fw-semibold uppercase tracking-wider">
                            Date {!! $loanThSort('loan_date') !!}
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button type="button" data-sort-col="direction" onclick="sortLoans('direction')" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-gray-500 hover:text-indigo-700 fw-semibold uppercase tracking-wider">
                            Type {!! $loanThSort('direction') !!}
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button type="button" data-sort-col="party" onclick="sortLoans('party')" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-gray-500 hover:text-indigo-700 fw-semibold uppercase tracking-wider">
                            Party {!! $loanThSort('party') !!}
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button type="button" data-sort-col="project" onclick="sortLoans('project')" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-gray-500 hover:text-indigo-700 fw-semibold uppercase tracking-wider">
                            Project {!! $loanThSort('project') !!}
                        </button>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button type="button" data-sort-col="amount" onclick="sortLoans('amount')" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-gray-500 hover:text-indigo-700 fw-semibold uppercase tracking-wider">
                            Amount {!! $loanThSort('amount') !!}
                        </button>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button type="button" data-sort-col="interest_rate" onclick="sortLoans('interest_rate')" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-gray-500 hover:text-indigo-700 fw-semibold uppercase tracking-wider">
                            Rate {!! $loanThSort('interest_rate') !!}
                        </button>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Interest</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Payable</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button type="button" data-sort-col="payment_method" onclick="sortLoans('payment_method')" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-gray-500 hover:text-indigo-700 fw-semibold uppercase tracking-wider">
                            Method {!! $loanThSort('payment_method') !!}
                        </button>
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <button type="button" data-sort-col="bank" onclick="sortLoans('bank')" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-gray-500 hover:text-indigo-700 fw-semibold uppercase tracking-wider">
                            Bank {!! $loanThSort('bank') !!}
                        </button>
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($loans as $loan)
                    @php
                        $principal = (float) ($loan->amount ?? 0);
                        $rate = (float) ($loan->interest_rate ?? 0);
                        $endDateForCalc = isset($endDate) && $endDate ? \Carbon\Carbon::parse($endDate)->endOfDay() : now();
                        $isApproved = $loan->isApproved();
                        $outstanding = ($loan->direction === 'received' || ($loan->direction === 'repaid' && $isApproved))
                            ? $loan->outstandingAsOf($endDateForCalc)
                            : null;
                        $interestAmount = $outstanding ? (float) ($outstanding['interest_due'] ?? 0) : 0.0;
                        $payableAmount = $outstanding
                            ? (float) ($outstanding['total_due'] ?? 0)
                            : ($loan->direction === 'received' ? $principal : 0.0);
                        $isClosed = (bool) ($loan->is_closed ?? false) || (bool) ($outstanding['is_closed'] ?? false);
                        $partyLabel =
                            $loan->party_source
                                ?? $loan->party_name
                                ?? $loan->source
                                ?? ($loan->supplier ? $loan->supplier->name : ($loan->staff ? $loan->staff->name : '—'));
                        $paymentCollapseId = 'loan-payments-' . $loan->id;
                        $loanPayload = [
                            'id' => $loan->id,
                            'direction' => $loan->direction,
                            'loan_date' => optional($loan->loan_date)->format('Y-m-d'),
                            'amount' => (float) ($loan->amount ?? 0),
                            'interest_rate' => (float) ($loan->interest_rate ?? 0),
                            'project_id' => $loan->project_id,
                            'supplier_id' => $loan->supplier_id,
                            'staff_id' => $loan->staff_id,
                            'party_source' => $loan->party_source,
                            'payment_method' => $loan->payment_method,
                            'bank_account_id' => $loan->bank_account_id,
                            'reference_number' => $loan->reference_number,
                            'notes' => $loan->notes,
                        ];
                    @endphp
                    <tr class="loan-row-toggle hover:bg-gray-50 transition-colors cursor-pointer"
                        role="button"
                        tabindex="0"
                        onclick="handleLoanRowToggle(event, this)"
                        onkeydown="handleLoanRowToggleKeydown(event, this)"
                        aria-controls="{{ $paymentCollapseId }}"
                        aria-expanded="false">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $loan->loan_date->format('M d, Y') }}</div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex flex-wrap gap-1">
                                @if($loan->direction === 'received')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Taken</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Given</span>
                                @endif
                                @if($isClosed)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Closed</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900">{{ $partyLabel }}</div>
                            @if($loan->reference_number)
                                <div class="text-xs text-gray-500">Ref: {{ $loan->reference_number }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-gray-700">{{ $loan->project ? $loan->project->name : '—' }}</div>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <span class="text-sm font-semibold {{ $loan->direction === 'received' ? 'text-green-600' : 'text-red-600' }}">
                                ${{ number_format($loan->amount, 2) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap text-sm text-gray-600">{{ number_format($rate, 2) }}%</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap text-sm text-gray-600">
                            ${{ number_format($outstanding ? $interestAmount : 0, 2) }}
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if($loan->direction === 'received')
                                <div class="text-sm font-semibold text-green-600">${{ number_format($payableAmount, 2) }}</div>
                            @elseif($isApproved && $outstanding)
                                <div class="text-sm font-semibold text-red-600">${{ number_format((float) ($outstanding['principal_outstanding'] ?? 0), 2) }}</div>
                                <div class="text-xs text-gray-500">Outstanding</div>
                            @else
                                <div class="text-sm font-semibold text-red-600">${{ number_format($principal, 2) }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                            {{ $loan->payment_method ? ucfirst(str_replace('_', ' ', $loan->payment_method)) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ $loan->bankAccount ? $loan->bankAccount->account_name : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center align-middle loan-approval-cell whitespace-nowrap" onclick="event.stopPropagation();">
                            @if($isApproved)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Approved</span>
                                <div class="text-xs text-gray-400 mt-1">{{ $loan->approved_at->format('M d, Y') }}</div>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                                @if(Auth::user()->isAdmin())
                                    <form action="{{ route('admin.loans.approve', $loan) }}" method="POST" class="mt-1.5 mb-0" onclick="event.stopPropagation();">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success btn-keep-text">Approve</button>
                                    </form>
                                @endif
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap" onclick="event.stopPropagation();">
                            <div class="d-flex gap-1 justify-content-end loan-row-actions">
                                @if($loan->direction === 'received' && !$isClosed && $isApproved)
                                    <button type="button"
                                            class="btn btn-sm btn-outline-primary loan-pay-btn"
                                            data-loan-id="{{ $loan->id }}"
                                            data-party-label="{{ e($partyLabel) }}"
                                            data-payment-mode="payment"
                                            title="Record payment">
                                        <i class="bi bi-cash-coin"></i>
                                    </button>
                                @endif
                                @if($loan->direction === 'repaid' && !$isClosed && $isApproved)
                                    <button type="button"
                                            class="btn btn-sm btn-outline-success loan-return-btn"
                                            data-loan-id="{{ $loan->id }}"
                                            data-party-label="{{ e($partyLabel) }}"
                                            data-payment-mode="return"
                                            title="Record return">
                                        <i class="bi bi-arrow-return-left"></i>
                                    </button>
                                @endif
                                @if(!$isApproved)
                                    <button type="button" class="btn btn-sm btn-outline-warning"
                                            onclick='openLoanCrudModal("edit", @json($loanPayload), event)'
                                            title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form action="{{ route('admin.loans.destroy', $loan) }}" method="POST" id="delete-loan-form-{{ $loan->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="confirmLoanDelete({{ $loan->id }}, event)"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    <tr class="loan-payments-row">
                        <td colspan="12" class="p-0">
                            <div id="{{ $paymentCollapseId }}" class="loan-payments-collapse d-none bg-gray-50 border-t border-gray-100 px-4 sm:px-6 py-4">
                                @php
                                    $payments = $loan->payments ?? collect();
                                    $paymentsTotal = (float) $payments->sum('amount');
                                    $isGivenLoan = $loan->direction === 'repaid';
                                    $historyTitle = $isGivenLoan ? 'Return history' : 'Payment history';
                                    $amountColLabel = $isGivenLoan ? 'Return amount' : 'Paid amount';
                                @endphp
                                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $historyTitle }}</div>
                                        @if($isGivenLoan && $isApproved)
                                            <div class="text-xs text-gray-500 mt-0.5">Given: ${{ number_format($principal, 2) }}
                                                @if($payments->isNotEmpty())
                                                    · Returned: ${{ number_format($paymentsTotal, 2) }}
                                                    @if($outstanding)
                                                        · Still due: ${{ number_format((float) ($outstanding['total_due'] ?? 0), 2) }}
                                                    @endif
                                                @endif
                                            </div>
                                        @elseif(!$isGivenLoan && $payments->isNotEmpty())
                                            <div class="text-xs text-gray-500 mt-0.5">Total paid: ${{ number_format($paymentsTotal, 2) }}</div>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-400">Click row again to hide</div>
                                </div>

                                @if($isGivenLoan && !$isApproved)
                                    <div class="text-sm text-gray-500">Approve this given loan to record and view returns here.</div>
                                @elseif($payments->isEmpty())
                                    <div class="text-sm text-gray-500">{{ $isGivenLoan ? 'No returns recorded yet.' : 'No payments recorded yet.' }}</div>
                                @else
                                    <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
                                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ $amountColLabel }}</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Interest</th>
                                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Principal</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-100">
                                                @foreach($payments as $p)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-3 py-2 text-gray-700">{{ $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('M d, Y') : '—' }}</td>
                                                        <td class="px-3 py-2 text-right font-semibold {{ $isGivenLoan ? 'text-green-600' : 'text-gray-900' }}">${{ number_format((float) $p->amount, 2) }}</td>
                                                        <td class="px-3 py-2 text-right text-gray-600">${{ number_format((float) ($p->interest_paid ?? 0), 2) }}</td>
                                                        <td class="px-3 py-2 text-right text-gray-600">${{ number_format((float) ($p->principal_paid ?? 0), 2) }}</td>
                                                        <td class="px-3 py-2 text-gray-600">{{ $p->payment_method ? ucfirst(str_replace('_', ' ', $p->payment_method)) : '—' }}</td>
                                                        <td class="px-3 py-2 text-gray-500">{{ $p->notes ?? '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 py-2 text-left text-xs font-semibold text-gray-700">Total</th>
                                                    <th class="px-3 py-2 text-right text-sm font-semibold text-gray-900">${{ number_format($paymentsTotal, 2) }}</th>
                                                    <th colspan="4"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="px-4 py-10 text-center text-sm text-gray-500">
                            No loan transactions found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-4 sm:px-6 py-4 border-t border-gray-200">
        <x-pagination :paginator="$loans" wrapper-class="mt-0" />
    </div>
</div>
@endsection

@push('modals')
<!-- Add/Edit Loan Modal -->
<div class="modal fade" id="loanCrudModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loanCrudModalTitle">Add Loan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="loanCrudErrors" class="alert alert-danger d-none"></div>
                <form id="loanCrudForm">
                    @csrf
                    <input type="hidden" id="loanCrudMethod" value="POST">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Direction *</label>
                            <select name="direction" class="form-select" required onchange="updateLoanCrudDirectionUI()">
                                <option value="received">Taken</option>
                                <option value="repaid">Given</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Loan Date *</label>
                            <input type="date" name="loan_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Amount *</label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                        </div>
                        <div class="col-md-3" id="loanCrudInterestWrap">
                            <label class="form-label">Interest Rate (%)</label>
                            <input type="number" step="0.01" min="0" max="1000" name="interest_rate" class="form-control" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Project</label>
                            <select name="project_id" class="form-select">
                                <option value="">None</option>
                                @foreach($projects ?? [] as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label" id="loanCrudPartyLabel">Lender / Source</label>
                            <input type="text" name="party_source" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label" for="loan_crud_payment_method">Payment Method</label>
                            @include('admin.partials.payment-method-select', [
                                'id' => 'loan_crud_payment_method',
                                'selected' => '',
                            ])
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Bank Account</label>
                            <select name="bank_account_id" class="form-select">
                                <option value="">None</option>
                                @foreach($bankAccounts ?? [] as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Reference #</label>
                            <input type="text" name="reference_number" class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="loanCrudSaveBtn" onclick="submitLoanCrudForm()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="loanPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loanPaymentModalTitle">Record Loan Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <div><strong>Party:</strong> <span id="loanPaymentParty">—</span></div>
                    <div class="mt-2 row g-2">
                        <div class="col-md-4"><strong>Principal Outstanding:</strong> $<span id="loanOutstandingPrincipal">0.00</span></div>
                        <div class="col-md-4"><strong>Interest Due:</strong> $<span id="loanOutstandingInterest">0.00</span></div>
                        <div class="col-md-4"><strong>Total Due:</strong> $<span id="loanOutstandingTotal">0.00</span></div>
                    </div>
                    <div class="text-muted small mt-2" id="loanPaymentModalHelp">Payment allocation: Interest first, then Principal. If fully paid, loan will be closed.</div>
                </div>

                <form method="POST" id="loanPaymentForm" action="#">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Payment Date</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label" for="loan_payment_payment_method">Payment Method</label>
                            @include('admin.partials.payment-method-select', [
                                'id' => 'loan_payment_payment_method',
                                'selected' => '',
                            ])
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bank Account</label>
                            <select name="bank_account_id" class="form-select">
                                <option value="">None</option>
                                @foreach($bankAccounts ?? [] as $acc)
                                    <option value="{{ $acc->id }}">{{ $acc->account_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Reference #</label>
                            <input type="text" name="reference_number" class="form-control" placeholder="Cheque / Txn ID">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="loanPaymentModalSubmitBtn" onclick="submitLoanPayment()">Save Payment</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal fade" id="loanDeleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="loanDeleteConfirmMessage" class="alert alert-warning mb-0">
                    This will permanently delete the loan transaction.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitLoanDelete()">Delete</button>
            </div>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    let loanSortColumn = @json($sortColumn ?? request('sort', 'loan_date'));
    let loanSortDir = @json($sortDir ?? request('sort_dir', 'desc'));

    function sortLoans(column) {
        if (loanSortColumn === column) {
            loanSortDir = loanSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            loanSortColumn = column;
            loanSortDir = (column === 'loan_date' || column === 'amount' || column === 'interest_rate') ? 'desc' : 'asc';
        }
        const sortEl = document.getElementById('loan_filter_sort');
        const dirEl = document.getElementById('loan_filter_sort_dir');
        if (sortEl) sortEl.value = loanSortColumn;
        if (dirEl) dirEl.value = loanSortDir;
        applyLoanFilters(null, true);
    }

    let loanCrudModalInstance = null;
    let loanPaymentModalInstance = null;
    let currentLoanId = null;
    let loanPaymentModalEl = null;
    let loanPaymentPartyEl = null;
    let loanPaymentFormEl = null;

    function resolveLoanPaymentElements() {
        loanPaymentModalEl = document.getElementById('loanPaymentModal');
        loanPaymentPartyEl = document.getElementById('loanPaymentParty');
        loanPaymentFormEl = document.getElementById('loanPaymentForm');
        return !!(loanPaymentModalEl && loanPaymentFormEl);
    }

    function ensureLoanPaymentModalInBody() {
        const modal = document.getElementById('loanPaymentModal');
        if (modal && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    }

    function resolveLoanCrudElements() {
        const modalEl = document.getElementById('loanCrudModal');
        const formEl = document.getElementById('loanCrudForm');
        const errorsEl = document.getElementById('loanCrudErrors');
        const titleEl = document.getElementById('loanCrudModalTitle');
        const saveBtn = document.getElementById('loanCrudSaveBtn');
        return { modalEl, formEl, errorsEl, titleEl, saveBtn };
    }

    function setLoanCrudFormValues(values = {}) {
        const { formEl } = resolveLoanCrudElements();
        if (!formEl) return;
        ['direction', 'loan_date', 'amount', 'interest_rate', 'project_id', 'party_source', 'payment_method', 'bank_account_id', 'reference_number', 'notes']
            .forEach((name) => {
                const input = formEl.querySelector(`[name="${name}"]`);
                if (!input) return;
                const val = values[name] ?? '';
                input.value = val === null ? '' : val;
            });
    }

    function updateLoanCrudDirectionUI() {
        const { formEl } = resolveLoanCrudElements();
        if (!formEl) return;
        const direction = formEl.querySelector('[name="direction"]')?.value || 'received';
        const partyLabel = document.getElementById('loanCrudPartyLabel');

        if (direction === 'repaid') {
            if (partyLabel) partyLabel.textContent = 'Receiver / Given To';
        } else {
            if (partyLabel) partyLabel.textContent = 'Lender / Source';
        }
    }

    function openLoanCrudModal(mode, loan, evt) {
        if (evt) {
            evt.preventDefault();
            evt.stopPropagation();
        }
        const crudModal = document.getElementById('loanCrudModal');
        if (crudModal && crudModal.parentElement !== document.body) {
            document.body.appendChild(crudModal);
        }
        const { modalEl, formEl, errorsEl, titleEl, saveBtn } = resolveLoanCrudElements();
        if (!modalEl || !formEl) return;

        errorsEl?.classList.add('d-none');
        errorsEl && (errorsEl.innerHTML = '');

        if (mode === 'edit' && loan) {
            titleEl.textContent = 'Edit Loan';
            saveBtn.textContent = 'Update';
            formEl.dataset.action = `/admin/loans/${loan.id}`;
            document.getElementById('loanCrudMethod').value = 'PUT';
            setLoanCrudFormValues(loan);
            updateLoanCrudDirectionUI();
        } else {
            titleEl.textContent = 'Add Loan';
            saveBtn.textContent = 'Save';
            formEl.dataset.action = `{{ route('admin.loans.store') }}`;
            document.getElementById('loanCrudMethod').value = 'POST';
            setLoanCrudFormValues({
                direction: 'received',
                loan_date: '{{ date('Y-m-d') }}',
                amount: '',
                interest_rate: 0,
            });
            updateLoanCrudDirectionUI();
        }

        loanCrudModalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
        loanCrudModalInstance.show();
    }

    async function submitLoanCrudForm() {
        const { formEl, errorsEl, saveBtn } = resolveLoanCrudElements();
        if (!formEl || !saveBtn) return;
        saveBtn.disabled = true;
        if (errorsEl) {
            errorsEl.classList.add('d-none');
            errorsEl.innerHTML = '';
        }

        const method = document.getElementById('loanCrudMethod').value || 'POST';
        const formData = new FormData(formEl);
        if (method === 'PUT') {
            formData.append('_method', 'PUT');
        }

        try {
            const resp = await fetch(formEl.dataset.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: formData,
            });

            if (!resp.ok) {
                const data = await resp.json().catch(() => ({}));
                if (data.errors) {
                    const messages = Object.values(data.errors).flat().map((m) => `<li>${m}</li>`).join('');
                    if (errorsEl) {
                        errorsEl.innerHTML = `<ul class="mb-0">${messages}</ul>`;
                        errorsEl.classList.remove('d-none');
                    }
                } else {
                    if (errorsEl) {
                        errorsEl.textContent = data.message || 'Failed to save loan.';
                        errorsEl.classList.remove('d-none');
                    }
                }
                return;
            }

            loanCrudModalInstance?.hide();
            await reloadLoanSections();
        } catch (err) {
            if (errorsEl) {
                errorsEl.textContent = 'Request failed. Please try again.';
                errorsEl.classList.remove('d-none');
            }
        } finally {
            saveBtn.disabled = false;
        }
    }

    async function reloadLoanSections() {
        const resp = await fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const html = await resp.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');

        const newSummary = doc.getElementById('loanSummaryCards');
        const newTable = doc.getElementById('loanTableSection');
        const curSummary = document.getElementById('loanSummaryCards');
        const curTable = document.getElementById('loanTableSection');

        if (newSummary && curSummary) curSummary.innerHTML = newSummary.innerHTML;
        if (newTable && curTable) curTable.innerHTML = newTable.innerHTML;
    }

    async function applyLoanFilters(evt = null, auto = false) {
        if (evt) {
            evt.preventDefault();
        }

        const form = document.getElementById('loanFilterForm');
        if (!form) return;

        const formData = new FormData(form);
        const params = new URLSearchParams();
        for (const [key, value] of formData.entries()) {
            if (value !== null && value !== '') {
                params.append(key, value);
            }
        }

        const url = form.action + (params.toString() ? `?${params.toString()}` : '');
        const summary = document.getElementById('loanSummaryCards');
        const table = document.getElementById('loanTableSection');
        if (summary) summary.style.opacity = '0.6';
        if (table) table.style.opacity = '0.6';

        try {
            const resp = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const html = await resp.text();
            const doc = new DOMParser().parseFromString(html, 'text/html');

            const newSummary = doc.getElementById('loanSummaryCards');
            const newTable = doc.getElementById('loanTableSection');
            if (newSummary && summary) summary.innerHTML = newSummary.innerHTML;
            if (newTable && table) table.innerHTML = newTable.innerHTML;

            window.history.pushState({ path: url }, '', url);
        } catch (e) {
            if (!auto) {
                alert('Failed to apply filter. Please try again.');
            }
        } finally {
            if (summary) summary.style.opacity = '1';
            if (table) table.style.opacity = '1';
        }
    }

    function resetLoanFilters() {
        const form = document.getElementById('loanFilterForm');
        if (!form) return;
        form.reset();
        const project = form.querySelector('[name="project_id"]');
        const startDate = form.querySelector('[name="start_date"]');
        const endDate = form.querySelector('[name="end_date"]');
        const direction = form.querySelector('[name="direction"]');
        const perPage = form.querySelector('[name="per_page"]');
        const sortInp = document.getElementById('loan_filter_sort');
        const sortDirInp = document.getElementById('loan_filter_sort_dir');
        if (project) project.value = '';
        if (startDate) startDate.value = '';
        if (endDate) endDate.value = '';
        if (direction) direction.value = '';
        if (perPage) perPage.value = '15';
        if (sortInp) sortInp.value = 'loan_date';
        if (sortDirInp) sortDirInp.value = 'desc';
        loanSortColumn = 'loan_date';
        loanSortDir = 'desc';
        applyLoanFilters(null, true);
    }

    let loanPaymentMode = 'payment';
    const loanPaymentStoreUrlBase = @json(url('/admin/loans'));

    window.openLoanPaymentModal = function openLoanPaymentModal(loanId, partyLabel, evt, mode = 'payment') {
        if (evt) {
            evt.preventDefault();
            evt.stopPropagation();
        }

        ensureLoanPaymentModalInBody();

        if (!resolveLoanPaymentElements()) {
            console.error('Loan payment modal not found in DOM.');
            return;
        }

        const waitForBootstrap = (attempt, cb) => {
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                cb();
                return;
            }
            if (attempt > 40) {
                console.error('Bootstrap Modal not available.');
                return;
            }
            setTimeout(() => waitForBootstrap(attempt + 1, cb), 50);
        };

        waitForBootstrap(0, function () {
        loanPaymentMode = mode === 'return' ? 'return' : 'payment';
        currentLoanId = loanId;
        if (loanPaymentPartyEl) {
            loanPaymentPartyEl.textContent = partyLabel || '—';
        }
        loanPaymentFormEl.action = `${loanPaymentStoreUrlBase}/${loanId}/payments`;

        const titleEl = document.getElementById('loanPaymentModalTitle');
        const helpEl = document.getElementById('loanPaymentModalHelp');
        const submitBtn = document.getElementById('loanPaymentModalSubmitBtn');
        if (loanPaymentMode === 'return') {
            if (titleEl) titleEl.textContent = 'Record Loan Return';
            if (helpEl) helpEl.textContent = 'Money returned against a given loan. Interest is applied first, then principal. Full return closes the loan and adds an Income entry.';
            if (submitBtn) {
                submitBtn.textContent = 'Save Return';
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-success');
            }
        } else {
            if (titleEl) titleEl.textContent = 'Record Loan Payment';
            if (helpEl) helpEl.textContent = 'Payment allocation: Interest first, then Principal. If fully paid, loan will be closed.';
            if (submitBtn) {
                submitBtn.textContent = 'Save Payment';
                submitBtn.classList.remove('btn-success');
                submitBtn.classList.add('btn-primary');
            }
        }

        // Always recreate fresh modal instance to prevent stale transition state
        // after multiple opens.
        if (loanPaymentModalInstance) {
            try { loanPaymentModalInstance.dispose(); } catch (e) {}
            loanPaymentModalInstance = null;
        }

        // Ensure modal element is in a clean state before showing again.
        loanPaymentModalEl.classList.remove('show');
        loanPaymentModalEl.style.display = 'none';
        loanPaymentModalEl.setAttribute('aria-hidden', 'true');

        loanPaymentModalInstance = new bootstrap.Modal(loanPaymentModalEl, {
            backdrop: true,
            keyboard: true,
            focus: true,
        });
        loanPaymentModalInstance.show();

        refreshLoanOutstanding();
        });
    }

    function refreshLoanOutstanding() {
        if (!currentLoanId) return;
        const dateEl = document.querySelector('#loanPaymentForm input[name="payment_date"]');
        const asOf = dateEl?.value || '{{ date('Y-m-d') }}';

        fetch(`${loanPaymentStoreUrlBase}/${currentLoanId}/outstanding?as_of=${encodeURIComponent(asOf)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('loanOutstandingPrincipal').textContent = (data.principal_outstanding || 0).toFixed(2);
            document.getElementById('loanOutstandingInterest').textContent = (data.interest_due || 0).toFixed(2);
            document.getElementById('loanOutstandingTotal').textContent = (data.total_due || 0).toFixed(2);
        })
        .catch(() => {});
    }

    document.addEventListener('input', function(e) {
        if (e.target && e.target.name === 'payment_date') {
            refreshLoanOutstanding();
        }
    });

    function submitLoanPayment() {
        const form = document.getElementById('loanPaymentForm');
        if (form) form.submit();
    }

    // Delete confirm (design) - submit only after user confirms
    let loanDeletePendingLoanId = null;

    function confirmLoanDelete(loanId, evt) {
        if (evt) {
            evt.preventDefault();
            evt.stopPropagation();
        }
        loanDeletePendingLoanId = loanId;

        const modalEl = document.getElementById('loanDeleteConfirmModal');
        if (!modalEl) return;
        const instance = bootstrap.Modal.getOrCreateInstance(modalEl);
        instance.show();
    }

    function submitLoanDelete() {
        if (!loanDeletePendingLoanId) return;
        const form = document.getElementById(`delete-loan-form-${loanDeletePendingLoanId}`);
        if (form) form.submit();
    }

    function toggleLoanPaymentRow(row) {
        const detailsRow = row?.nextElementSibling;
        const el = detailsRow ? detailsRow.querySelector('.loan-payments-collapse') : null;
        if (!el) return;

        const isHidden = el.classList.contains('d-none');
        if (isHidden) {
            el.classList.remove('d-none');
            row.setAttribute('aria-expanded', 'true');
        } else {
            el.classList.add('d-none');
            row.setAttribute('aria-expanded', 'false');
        }
    }

    function handleLoanRowToggle(e, row) {
        if (
            e.target.closest('.loan-row-actions') ||
            e.target.closest('.loan-approval-cell') ||
            e.target.closest('.loan-payments-collapse') ||
            e.target.closest('a, button, input, select, textarea, label, form')
        ) {
            return;
        }
        toggleLoanPaymentRow(row);
    }

    function handleLoanRowToggleKeydown(e, row) {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            toggleLoanPaymentRow(row);
        }
    }

    if (!window.__loanPaymentActionClickBound) {
        window.__loanPaymentActionClickBound = true;
        document.addEventListener('click', function(e) {
            const actionBtn = e.target.closest('.loan-pay-btn, .loan-return-btn');
            if (!actionBtn) {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            const loanId = actionBtn.getAttribute('data-loan-id');
            const partyLabel = actionBtn.getAttribute('data-party-label') || '';
            const mode = actionBtn.getAttribute('data-payment-mode') || 'payment';
            if (loanId) {
                openLoanPaymentModal(loanId, partyLabel, e, mode);
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        resolveLoanPaymentElements();
        resolveLoanCrudElements();

        // Ensure delete confirm message never gets hidden by other UI logic.
        const deleteModalEl = document.getElementById('loanDeleteConfirmModal');
        if (deleteModalEl) {
            deleteModalEl.addEventListener('show.bs.modal', function() {
                const msg = document.getElementById('loanDeleteConfirmMessage');
                if (msg) {
                    msg.classList.remove('d-none');
                    msg.style.display = '';
                }
            });
        }

        // Payment modal lifecycle cleanup to support unlimited open/close.
        if (loanPaymentModalEl) {
            loanPaymentModalEl.addEventListener('hide.bs.modal', function() {
                const active = document.activeElement;
                if (active && loanPaymentModalEl.contains(active) && typeof active.blur === 'function') {
                    active.blur();
                }
            });

            loanPaymentModalEl.addEventListener('hidden.bs.modal', function() {
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
                document.querySelectorAll('.modal-backdrop').forEach(function(el) {
                    el.remove();
                });

                if (loanPaymentModalInstance) {
                    try { loanPaymentModalInstance.dispose(); } catch (e) {}
                    loanPaymentModalInstance = null;
                }
            });
        }
    });
</script>
@endpush

