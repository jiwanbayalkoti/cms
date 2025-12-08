@extends('admin.layout')

@section('title', 'Bank Account Ledger')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Bank Account Ledger</h1>
        <p class="text-muted mb-0">{{ $bankAccount->account_name }}</p>
    </div>
    <a href="{{ route('admin.bank-accounts.show', $bankAccount) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Account
    </a>
</div>

@if(!$bankAccount->chart_of_account_id)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>This bank account is not linked to a chart of account. Please link it to view the ledger.
    </div>
@else
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Filter Transactions</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.bank-accounts.ledger', $bankAccount) }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}" required>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.bank-accounts.ledger', $bankAccount) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Opening Balance</h6>
                    <h4 class="text-primary mb-0">{{ number_format($openingBalance, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Debit</h6>
                    <h4 class="text-success mb-0">
                        {{ number_format($transactions->where('entry_type', 'debit')->sum('amount'), 2) }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Total Credit</h6>
                    <h4 class="text-danger mb-0">
                        {{ number_format($transactions->where('entry_type', 'credit')->sum('amount'), 2) }}
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Closing Balance</h6>
                    <h4 class="text-info mb-0">{{ number_format($runningBalance, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Transaction Ledger</h5>
            <span class="badge bg-secondary">{{ $transactions->count() }} Transactions</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Entry #</th>
                            <th>Description</th>
                            <th>Reference</th>
                            <th class="text-end">Debit</th>
                            <th class="text-end">Credit</th>
                            <th class="text-end">Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="table-info">
                            <td colspan="4"><strong>Opening Balance</strong></td>
                            <td colspan="2"></td>
                            <td class="text-end"><strong>{{ number_format($openingBalance, 2) }}</strong></td>
                            <td></td>
                        </tr>
                        @forelse($transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->journalEntry->entry_date->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ route('admin.journal-entries.show', $transaction->journalEntry) }}" class="text-decoration-none">
                                        <strong>{{ $transaction->journalEntry->entry_number }}</strong>
                                    </a>
                                </td>
                                <td>
                                    {{ $transaction->description ?? $transaction->journalEntry->description ?? '—' }}
                                </td>
                                <td>{{ $transaction->journalEntry->reference ?? '—' }}</td>
                                <td class="text-end">
                                    @if($transaction->entry_type === 'debit')
                                        <span class="text-success fw-semibold">{{ number_format($transaction->amount, 2) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($transaction->entry_type === 'credit')
                                        <span class="text-danger fw-semibold">{{ number_format($transaction->amount, 2) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong class="{{ $transaction->running_balance >= 0 ? 'text-primary' : 'text-danger' }}">
                                        {{ number_format($transaction->running_balance, 2) }}
                                    </strong>
                                </td>
                                <td>
                                    <a href="{{ route('admin.journal-entries.show', $transaction->journalEntry) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <p class="text-muted mb-0">No transactions found for the selected date range.</p>
                                    <small class="text-muted">Transactions will appear here once journal entries are posted.</small>
                                </td>
                            </tr>
                        @endforelse
                        @if($transactions->count() > 0)
                            <tr class="table-primary fw-bold">
                                <td colspan="4"><strong>Closing Balance</strong></td>
                                <td class="text-end">
                                    <strong class="text-success">{{ number_format($transactions->where('entry_type', 'debit')->sum('amount'), 2) }}</strong>
                                </td>
                                <td class="text-end">
                                    <strong class="text-danger">{{ number_format($transactions->where('entry_type', 'credit')->sum('amount'), 2) }}</strong>
                                </td>
                                <td class="text-end">
                                    <strong class="text-primary fs-5">{{ number_format($runningBalance, 2) }}</strong>
                                </td>
                                <td></td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($transactions->count() > 0)
        <div class="mt-3 text-end">
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="bi bi-printer me-1"></i> Print Ledger
            </button>
        </div>
    @endif
@endif
@endsection

