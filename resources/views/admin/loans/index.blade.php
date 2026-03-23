@extends('admin.layout')

@section('title', 'Loans')

@section('content')
<div class="mb-6 flex justify-between items-center gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Loans</h1>
        <p class="mt-2 text-gray-600">Record loan received/repaid separately from Income.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.reports.loans') }}" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-text me-1"></i>Loans Report
        </a>
        <button type="button" class="btn btn-success" onclick="openLoanCrudModal('create', null, event)">
            <i class="bi bi-plus-circle me-1"></i>Add Loan
        </button>
    </div>
</div>

<div class="bg-white shadow-lg rounded-lg p-4 mb-6">
    <form method="GET" action="{{ route('admin.loans.index') }}" class="row g-3" id="loanFilterForm" onsubmit="applyLoanFilters(event)">
        <input type="hidden" name="sort" id="loan_filter_sort" value="{{ $sortColumn ?? request('sort', 'loan_date') }}">
        <input type="hidden" name="sort_dir" id="loan_filter_sort_dir" value="{{ $sortDir ?? request('sort_dir', 'desc') }}">
        <div class="col-md-3">
            <label class="form-label small">Project</label>
            <select name="project_id" class="form-select form-select-sm" onchange="applyLoanFilters(null, true)">
                <option value="">All Projects</option>
                @foreach($projects ?? [] as $p)
                    <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small">Start Date</label>
            <input type="date" name="start_date" value="{{ request('start_date', $startDate ?? '') }}" class="form-control form-control-sm" onchange="applyLoanFilters(null, true)">
        </div>
        <div class="col-md-2">
            <label class="form-label small">End Date</label>
            <input type="date" name="end_date" value="{{ request('end_date', $endDate ?? '') }}" class="form-control form-control-sm" onchange="applyLoanFilters(null, true)">
        </div>
        <div class="col-md-2">
            <label class="form-label small">Direction</label>
            <select name="direction" class="form-select form-select-sm" onchange="applyLoanFilters(null, true)">
                <option value="">All</option>
                <option value="received" {{ request('direction') === 'received' ? 'selected' : '' }}>Taken</option>
                <option value="repaid" {{ request('direction') === 'repaid' ? 'selected' : '' }}>Given</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button type="button" onclick="resetLoanFilters()" class="btn btn-outline-secondary btn-sm w-100">
                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
            </button>
        </div>
    </form>
</div>

<div id="loanSummaryCards" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white border rounded-lg p-4">
        <div class="text-sm text-muted">Total Received</div>
        <div class="text-2xl font-bold text-green-600">${{ number_format($totalReceived ?? 0, 2) }}</div>
    </div>
    <div class="bg-white border rounded-lg p-4">
        <div class="text-sm text-muted">Total Repaid</div>
        <div class="text-2xl font-bold text-red-600">${{ number_format($totalRepaid ?? 0, 2) }}</div>
    </div>
    <div class="bg-white border rounded-lg p-4">
        <div class="text-sm text-muted">Net (Received - Repaid)</div>
        <div class="text-2xl font-bold {{ ($netBalance ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">${{ number_format($netBalance ?? 0, 2) }}</div>
    </div>
</div>

<div id="loanTableSection" class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="p-4 border-b d-flex justify-content-between align-items-center">
        <div class="fw-bold">Loan Transactions</div>
    </div>
    <div class="table-responsive">
        <table class="table table-sm table-striped align-middle mb-0">
            <thead class="table-light" id="loan-thead">
                @php
                    $sc = $sortColumn ?? request('sort', 'loan_date');
                    $sd = $sortDir ?? request('sort_dir', 'desc');
                    $loanThSort = function (string $col) use ($sc, $sd) {
                        $active = $sc === $col;
                        $icon = $active
                            ? ($sd === 'asc' ? 'bi-sort-up' : 'bi-sort-down')
                            : 'bi-arrow-down-up';
                        $cls = $active ? 'text-primary' : 'text-secondary';
                        return '<i class="bi '.$icon.' ms-1 '.$cls.'" aria-hidden="true"></i>';
                    };
                @endphp
                <tr>
                    <th>
                        <button type="button" data-sort-col="loan_date" onclick="sortLoans('loan_date')" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold">
                            Date {!! $loanThSort('loan_date') !!}
                        </button>
                    </th>
                    <th>
                        <button type="button" data-sort-col="direction" onclick="sortLoans('direction')" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold">
                            Direction {!! $loanThSort('direction') !!}
                        </button>
                    </th>
                    <th>
                        <button type="button" data-sort-col="party" onclick="sortLoans('party')" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold">
                            Party / Source {!! $loanThSort('party') !!}
                        </button>
                    </th>
                    <th>
                        <button type="button" data-sort-col="project" onclick="sortLoans('project')" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold">
                            Project {!! $loanThSort('project') !!}
                        </button>
                    </th>
                    <th class="text-end">
                        <button type="button" data-sort-col="amount" onclick="sortLoans('amount')" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold">
                            Amount {!! $loanThSort('amount') !!}
                        </button>
                    </th>
                    <th class="text-end">
                        <button type="button" data-sort-col="interest_rate" onclick="sortLoans('interest_rate')" class="btn btn-link btn-sm p-0 text-decoration-none text-dark fw-semibold">
                            Interest % {!! $loanThSort('interest_rate') !!}
                        </button>
                    </th>
                    <th class="text-end text-muted small">Interest</th>
                    <th class="text-end text-muted small">Payable</th>
                    <th>
                        <button type="button" data-sort-col="payment_method" onclick="sortLoans('payment_method')" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold">
                            Payment Method {!! $loanThSort('payment_method') !!}
                        </button>
                    </th>
                    <th>
                        <button type="button" data-sort-col="bank" onclick="sortLoans('bank')" class="btn btn-link btn-sm btn-keep-text p-0 text-decoration-none text-dark fw-semibold">
                            Bank {!! $loanThSort('bank') !!}
                        </button>
                    </th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($loans as $loan)
                    @php
                        $principal = (float) ($loan->amount ?? 0);
                        $rate = (float) ($loan->interest_rate ?? 0);
                        $loanDate = $loan->loan_date ? \Carbon\Carbon::parse($loan->loan_date)->startOfDay() : null;
                        $endDateForCalc = isset($endDate) && $endDate ? \Carbon\Carbon::parse($endDate)->endOfDay() : now();
                        $outstanding = $loan->direction === 'received'
                            ? $loan->outstandingAsOf($endDateForCalc)
                            : null;
                        $interestAmount = $loan->direction === 'received' ? (float) ($outstanding['interest_due'] ?? 0) : 0.0;
                        $payableAmount = $loan->direction === 'received' ? (float) ($outstanding['total_due'] ?? 0) : $principal;
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
                    <tr class="loan-row-toggle"
                        role="button"
                        tabindex="0"
                        onclick="handleLoanRowToggle(event, this)"
                        onkeydown="handleLoanRowToggleKeydown(event, this)"
                        aria-controls="{{ $paymentCollapseId }}"
                        aria-expanded="false">
                        <td>{{ $loan->loan_date->format('M d, Y') }}</td>
                        <td>
                            @if($loan->direction === 'received')
                                <span class="badge bg-success">Taken</span>
                                @if($isClosed)
                                    <span class="badge bg-secondary ms-1">Closed</span>
                                @endif
                            @else
                                <span class="badge bg-danger">Given</span>
                            @endif
                        </td>
                        <td>
                            {{ $partyLabel }}
                        </td>
                        <td>{{ $loan->project ? $loan->project->name : '—' }}</td>
                        <td class="text-end">
                            @if($loan->direction === 'received')
                                <span class="text-success fw-bold">${{ number_format($loan->amount, 2) }}</span>
                            @else
                                <span class="text-danger fw-bold">${{ number_format($loan->amount, 2) }}</span>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($rate, 2) }}%</td>
                        <td class="text-end">
                            @if($loan->direction === 'received')
                                ${{ number_format($interestAmount, 2) }}
                            @else
                                ${{ number_format(0, 2) }}
                            @endif
                        </td>
                        <td class="text-end">
                            @if($loan->direction === 'received')
                                <span class="text-success fw-bold">${{ number_format($payableAmount, 2) }}</span>
                            @else
                                <span class="text-danger fw-bold">${{ number_format($principal, 2) }}</span>
                            @endif
                        </td>
                        <td>{{ $loan->payment_method ?? '—' }}</td>
                        <td>{{ $loan->bankAccount ? $loan->bankAccount->account_name : '—' }}</td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end loan-row-actions">
                                @if($loan->direction === 'received' && !$isClosed)
                                    <button type="button"
                                            class="btn btn-sm btn-primary loan-pay-btn"
                                            data-loan-id="{{ $loan->id }}"
                                            data-party-label="{{ $partyLabel }}"
                                            onclick="openLoanPaymentModal({{ $loan->id }}, @js($partyLabel), event)"
                                            title="Pay">
                                        <i class="bi bi-cash-coin"></i>
                                    </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-outline-warning"
                                        onclick='openLoanCrudModal("edit", @json($loanPayload), event)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route('admin.loans.destroy', $loan) }}" method="POST" id="delete-loan-form-{{ $loan->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="confirmLoanDelete({{ $loan->id }}, event)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <tr class="bg-light loan-payments-row">
                        <td colspan="11" class="p-0">
                            <div id="{{ $paymentCollapseId }}" class="loan-payments-collapse d-none p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="fw-bold">Payments</div>
                                    <div class="text-muted small">Click row again to hide</div>
                                </div>

                                @if($loan->direction !== 'received')
                                    <div class="text-muted">Payments history is only for received loans.</div>
                                @else
                                    @php
                                        $payments = $loan->payments ?? collect();
                                    @endphp

                                    @if($payments->isEmpty())
                                        <div class="text-muted">No payments recorded yet.</div>
                                    @else
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="table-secondary">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th class="text-end">Paid Amount</th>
                                                        <th class="text-end">Interest Paid</th>
                                                        <th class="text-end">Principal Paid</th>
                                                        <th>Notes</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($payments as $p)
                                                        <tr>
                                                            <td>{{ $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('M d, Y') : '—' }}</td>
                                                            <td class="text-end">${{ number_format((float) $p->amount, 2) }}</td>
                                                            <td class="text-end">${{ number_format((float) ($p->interest_paid ?? 0), 2) }}</td>
                                                            <td class="text-end">${{ number_format((float) ($p->principal_paid ?? 0), 2) }}</td>
                                                            <td>{{ $p->notes ?? '—' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">No loan transactions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4">
        <x-pagination :paginator="$loans" wrapper-class="mt-0" />
    </div>
</div>

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
                            <label class="form-label">Payment Method</label>
                            <input type="text" name="payment_method" class="form-control">
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
                <h5 class="modal-title">Record Loan Payment</h5>
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
                    <div class="text-muted small mt-2">Payment allocation: Interest first, then Principal. If fully paid, loan will be closed.</div>
                </div>

                <form method="POST" id="loanPaymentForm">
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
                            <label class="form-label">Payment Method</label>
                            <input type="text" name="payment_method" class="form-control" placeholder="Cash / Bank Transfer">
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
                <button type="button" class="btn btn-primary" onclick="submitLoanPayment()">Save Payment</button>
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
        return !!(loanPaymentModalEl && loanPaymentPartyEl && loanPaymentFormEl);
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
        const sortInp = document.getElementById('loan_filter_sort');
        const sortDirInp = document.getElementById('loan_filter_sort_dir');
        if (project) project.value = '';
        if (startDate) startDate.value = '';
        if (endDate) endDate.value = '';
        if (direction) direction.value = '';
        if (sortInp) sortInp.value = 'loan_date';
        if (sortDirInp) sortDirInp.value = 'desc';
        loanSortColumn = 'loan_date';
        loanSortDir = 'desc';
        applyLoanFilters(null, true);
    }

    function openLoanPaymentModal(loanId, partyLabel, evt) {
        if (evt) {
            evt.preventDefault();
            evt.stopPropagation();
        }
        // Re-resolve on every open to handle re-rendered DOM states safely.
        if (!resolveLoanPaymentElements()) {
            return;
        }

        currentLoanId = loanId;
        loanPaymentPartyEl.textContent = partyLabel || '—';
        loanPaymentFormEl.action = `/admin/loans/${loanId}/payments`;

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
    }

    function refreshLoanOutstanding() {
        if (!currentLoanId) return;
        const dateEl = document.querySelector('#loanPaymentForm input[name="payment_date"]');
        const asOf = dateEl?.value || '{{ date('Y-m-d') }}';

        fetch(`/admin/loans/${currentLoanId}/outstanding?as_of=${encodeURIComponent(asOf)}`, {
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
@endsection

