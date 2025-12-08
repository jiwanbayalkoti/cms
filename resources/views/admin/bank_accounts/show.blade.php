@extends('admin.layout')

@section('title', 'Bank Account Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Bank/Cash Account Details</h1>
        <p class="text-muted mb-0">{{ $bankAccount->account_name }}</p>
    </div>
    <div>
        <a href="{{ route('admin.bank-accounts.ledger', $bankAccount) }}" class="btn btn-info me-2">
            <i class="bi bi-journal-text me-1"></i> View Ledger
        </a>
        <a href="{{ route('admin.bank-accounts.edit', $bankAccount) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Account Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Account Name:</th>
                        <td><strong>{{ $bankAccount->account_name }}</strong></td>
                    </tr>
                    <tr>
                        <th>Account Type:</th>
                        <td>
                            <span class="badge bg-{{ $bankAccount->account_type === 'bank' ? 'primary' : 'success' }}">
                                {{ ucfirst($bankAccount->account_type) }} Account
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Bank Name:</th>
                        <td>{{ $bankAccount->bank_name }}</td>
                    </tr>
                    @if($bankAccount->account_type === 'bank')
                        <tr>
                            <th>Account Number:</th>
                            <td>{{ $bankAccount->account_number ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Branch Name:</th>
                            <td>{{ $bankAccount->branch_name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>Branch Address:</th>
                            <td>{{ $bankAccount->branch_address ?? '—' }}</td>
                        </tr>
                        <tr>
                            <th>SWIFT Code:</th>
                            <td>{{ $bankAccount->swift_code ?? '—' }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Chart of Account:</th>
                        <td>
                            @if($bankAccount->chartOfAccount)
                                <a href="{{ route('admin.chart-of-accounts.show', $bankAccount->chartOfAccount) }}">
                                    {{ $bankAccount->chartOfAccount->account_code }} - {{ $bankAccount->chartOfAccount->account_name }}
                                </a>
                            @else
                                <span class="text-muted">Not linked</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Currency:</th>
                        <td>{{ $bankAccount->currency }}</td>
                    </tr>
                    <tr>
                        <th>Opening Balance:</th>
                        <td><strong>{{ number_format($bankAccount->opening_balance, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Current Balance:</th>
                        <td><strong class="text-primary fs-5">{{ number_format($bankAccount->current_balance, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Opening Date:</th>
                        <td>{{ $bankAccount->opening_balance_date?->format('Y-m-d') ?? '—' }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge rounded-pill {{ $bankAccount->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $bankAccount->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @if($bankAccount->notes)
                        <tr>
                            <th>Notes:</th>
                            <td>{{ $bankAccount->notes }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <a href="{{ route('admin.journal-entries.create') }}" class="btn btn-primary w-100 mb-2">
                    <i class="bi bi-journal-plus me-1"></i> Create Journal Entry
                </a>
                <a href="{{ route('admin.bank-accounts.ledger', $bankAccount) }}" class="btn btn-info w-100 mb-2">
                    <i class="bi bi-journal-text me-1"></i> View Ledger
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

