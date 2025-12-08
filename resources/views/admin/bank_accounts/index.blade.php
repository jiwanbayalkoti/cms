@extends('admin.layout')

@section('title', 'Bank & Cash Accounts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Bank & Cash Accounts</h1>
        <p class="text-muted mb-0">Manage bank and cash accounts</p>
    </div>
    <a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add Account
    </a>
</div>

@foreach(['bank' => 'Bank Accounts', 'cash' => 'Cash Accounts'] as $type => $label)
    @if(isset($bankAccounts[$type]) && $bankAccounts[$type]->count() > 0)
        <div class="card mb-4">
            <div class="card-header bg-{{ $type === 'bank' ? 'primary' : 'success' }} text-white">
                <h5 class="mb-0">{{ $label }}</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Account Name</th>
                                <th>Bank Name</th>
                                <th>Account Number</th>
                                <th>Branch</th>
                                <th class="text-end">Opening Balance</th>
                                <th class="text-end">Current Balance</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bankAccounts[$type] as $account)
                                <tr>
                                    <td><strong>{{ $account->account_name }}</strong></td>
                                    <td>{{ $account->bank_name }}</td>
                                    <td>{{ $account->account_number ?? '—' }}</td>
                                    <td>{{ $account->branch_name ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($account->opening_balance, 2) }}</td>
                                    <td class="text-end"><strong>{{ number_format($account->current_balance, 2) }}</strong></td>
                                    <td>
                                        <span class="badge rounded-pill {{ $account->is_active ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $account->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('admin.bank-accounts.show', $account) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bi bi-eye me-1"></i> View
                                            </a>
                                            <a href="{{ route('admin.bank-accounts.edit', $account) }}" class="btn btn-outline-warning btn-sm">
                                                <i class="bi bi-pencil me-1"></i> Edit
                                            </a>
                                            <a href="{{ route('admin.bank-accounts.ledger', $account) }}" class="btn btn-outline-info btn-sm">
                                                <i class="bi bi-journal-text me-1"></i> Ledger
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif
@endforeach

@if(($bankAccounts['bank'] ?? collect())->isEmpty() && ($bankAccounts['cash'] ?? collect())->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-bank display-1 text-muted"></i>
            <h4 class="mt-3">No Bank or Cash Accounts</h4>
            <p class="text-muted">Get started by creating your first bank or cash account</p>
            <a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-primary mt-3">
                <i class="bi bi-plus-circle me-2"></i> Create Account
            </a>
        </div>
    </div>
@endif
@endsection

