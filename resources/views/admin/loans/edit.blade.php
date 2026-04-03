@extends('admin.layout')

@section('title', 'Edit Loan')

@section('content')
<div class="mb-6 flex justify-between items-center gap-3">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Edit Loan</h1>
        <p class="mt-2 text-gray-600">Update loan transaction details.</p>
    </div>
    <div>
        <a href="{{ route('admin.loans.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.loans.update', $loan) }}" method="POST" id="loanForm">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Direction <span class="text-danger">*</span></label>
                    <select name="direction" class="form-select" required onchange="updateLoanDirectionUIEdit()">
                        <option value="received" {{ old('direction', $loan->direction) === 'received' ? 'selected' : '' }}>Taken</option>
                        <option value="repaid" {{ old('direction', $loan->direction) === 'repaid' ? 'selected' : '' }}>Given</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Loan Date <span class="text-danger">*</span></label>
                    <input type="date" name="loan_date" class="form-control" value="{{ old('loan_date', optional($loan->loan_date)->format('Y-m-d')) }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Amount <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount', $loan->amount) }}" required>
                </div>

                <div class="col-md-4" id="editInterestWrap">
                    <label class="form-label">Interest Rate (%)</label>
                    <input type="number" step="0.01" min="0" max="1000" name="interest_rate" id="interest_rate"
                           class="form-control" value="{{ old('interest_rate', $loan->interest_rate ?? 0) }}" placeholder="e.g., 10">
                </div>

                <div class="col-md-12">
                    @php
                        $amountPreview = (float) (old('amount', $loan->amount ?? 0) ?? 0);
                        $ratePreview = (float) (old('interest_rate', $loan->interest_rate ?? 0) ?? 0);
                        $interestPreview = $amountPreview * $ratePreview / 100;
                        $payablePreview = $amountPreview + $interestPreview;
                    @endphp
                    <div class="p-3 bg-light border rounded">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Interest Amount</span>
                            <strong>$<span id="interest_amount_preview_edit">{{ number_format($interestPreview, 2) }}</span></strong>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span class="text-muted">Total Payable</span>
                            <strong>$<span id="payable_amount_preview_edit">{{ number_format($payablePreview, 2) }}</span></strong>
                        </div>
                        <div class="text-muted small mt-2">Formula: payable = amount + (amount * interest_rate/100 * days/365)</div>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select">
                        <option value="">None</option>
                        @foreach($projects ?? [] as $p)
                            <option value="{{ $p->id }}" {{ (string) old('project_id', $loan->project_id) === (string) $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label" id="editPartyLabel">Lender / Source</label>
                    <input type="text" name="party_source" class="form-control"
                           value="{{ old('party_source', $loan->party_source ?? ($loan->party_name ?? $loan->source)) }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label" for="loan_edit_payment_method">Payment Method</label>
                    @include('admin.partials.payment-method-select', [
                        'id' => 'loan_edit_payment_method',
                        'selected' => old('payment_method', $loan->payment_method),
                    ])
                </div>

                <div class="col-md-4">
                    <label class="form-label">Bank Account</label>
                    <select name="bank_account_id" class="form-select">
                        <option value="">None</option>
                        @foreach($bankAccounts ?? [] as $acc)
                            <option value="{{ $acc->id }}" {{ (string) old('bank_account_id', $loan->bank_account_id) === (string) $acc->id ? 'selected' : '' }}>
                                {{ $acc->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Reference #</label>
                    <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number', $loan->reference_number) }}">
                </div>

                <div class="col-md-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $loan->notes) }}</textarea>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('admin.loans.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Loan</button>
            </div>
        </form>

        <script>
            function updateLoanPayablePreviewEdit() {
                const amountEl = document.querySelector('input[name="amount"]');
                const rateEl = document.querySelector('input[name="interest_rate"]');
                const dateEl = document.querySelector('input[name="loan_date"]');
                const amount = parseFloat(amountEl?.value || '0');
                const rate = parseFloat(rateEl?.value || '0');

                const loanDateStr = dateEl?.value;
                const loanDate = loanDateStr ? new Date(loanDateStr + 'T00:00:00') : null;
                const today = new Date();
                const endDate = new Date(today.getFullYear(), today.getMonth(), today.getDate()); // today 00:00
                const days = loanDate ? Math.max(0, Math.floor((endDate - loanDate) / (1000 * 60 * 60 * 24))) : 0;

                const interest = amount * rate / 100 * (days / 365);
                const payable = amount + interest;

                const interestOut = document.getElementById('interest_amount_preview_edit');
                const payableOut = document.getElementById('payable_amount_preview_edit');

                if (interestOut) interestOut.textContent = interest.toFixed(2);
                if (payableOut) payableOut.textContent = payable.toFixed(2);
            }

            document.addEventListener('input', function (e) {
                if (!e.target) return;
                if (e.target.name === 'amount' || e.target.name === 'interest_rate' || e.target.name === 'loan_date') {
                    updateLoanPayablePreviewEdit();
                }
            });

            function updateLoanDirectionUIEdit() {
                const direction = document.querySelector('select[name="direction"]')?.value || 'received';
                const partyLabel = document.getElementById('editPartyLabel');
                if (direction === 'repaid') {
                    if (partyLabel) partyLabel.textContent = 'Receiver / Given To';
                } else {
                    if (partyLabel) partyLabel.textContent = 'Lender / Source';
                }
                updateLoanPayablePreviewEdit();
            }

            updateLoanPayablePreviewEdit();
            updateLoanDirectionUIEdit();
        </script>
    </div>
</div>
@endsection

