@extends('admin.layout')

@section('title', 'Chart of Accounts')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Chart of Accounts</h1>
        <p class="text-muted mb-0">Manage your accounting accounts as per Nepal Accounting Standards</p>
    </div>
    <div>
        @if($accounts->isEmpty())
            <form action="{{ route('admin.chart-of-accounts.seed-defaults') }}" method="POST" class="d-inline me-2">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-download me-1"></i> Seed Default Accounts (NAS)
                </button>
            </form>
        @endif
        <a href="{{ route('admin.chart-of-accounts.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add New Account
        </a>
    </div>
</div>

@if($accounts->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-inbox display-1 text-muted"></i>
            <h4 class="mt-3">No Chart of Accounts Found</h4>
            <p class="text-muted">Get started by seeding default accounts based on Nepal Accounting Standards</p>
            <form action="{{ route('admin.chart-of-accounts.seed-defaults') }}" method="POST" class="mt-3">
                @csrf
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-download me-2"></i> Seed Default Chart of Accounts
                </button>
            </form>
        </div>
    </div>
@else
    @foreach(['asset' => 'Assets', 'liability' => 'Liabilities', 'equity' => 'Equity', 'revenue' => 'Revenue', 'expense' => 'Expenses'] as $type => $label)
        @if(isset($accounts[$type]) && $accounts[$type]->count() > 0)
            <div class="card mb-4">
                <div class="card-header bg-{{ $type === 'asset' ? 'primary' : ($type === 'liability' ? 'warning' : ($type === 'equity' ? 'info' : ($type === 'revenue' ? 'success' : 'danger'))) }} text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-{{ $type === 'asset' ? 'wallet' : ($type === 'liability' ? 'credit-card' : ($type === 'equity' ? 'pie-chart' : ($type === 'revenue' ? 'arrow-up-circle' : 'arrow-down-circle'))) }} me-2"></i>
                        {{ $label }} ({{ $accounts[$type]->count() }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Account Code</th>
                                    <th>Account Name</th>
                                    <th>Category</th>
                                    <th>Parent Account</th>
                                    <th class="text-end">Opening Balance</th>
                                    <th class="text-end">Current Balance</th>
                                    <th>Status</th>
                                    <th style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($accounts[$type] as $account)
                                    <tr class="{{ $account->level === 1 ? 'table-primary' : ($account->level === 2 ? 'fw-semibold' : '') }}">
                                        <td>
                                            <span class="badge bg-secondary">{{ $account->account_code }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @for($i = 1; $i < $account->level; $i++)
                                                    <span class="me-2">—</span>
                                                @endfor
                                                {{ $account->account_name }}
                                                @if($account->is_system)
                                                    <span class="badge bg-info ms-2">System</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($account->account_category)
                                                <span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $account->account_category)) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($account->parentAccount)
                                                <small class="text-muted">{{ $account->parentAccount->account_code }} - {{ $account->parentAccount->account_name }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="{{ $account->balance_type === 'debit' ? 'text-success' : 'text-danger' }}">
                                                {{ number_format($account->opening_balance, 2) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-semibold">
                                                {{ number_format($account->current_balance, 2) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge rounded-pill {{ $account->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $account->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('admin.chart-of-accounts.show', $account) }}" class="btn btn-outline-primary btn-sm" title="View">
                                                    <i class="bi bi-eye me-1"></i> View
                                                </a>
                                                <a href="{{ route('admin.chart-of-accounts.edit', $account) }}" class="btn btn-outline-warning btn-sm" title="Edit">
                                                    <i class="bi bi-pencil me-1"></i> Edit
                                                </a>
                                                @if(!$account->is_system)
                                                    <form action="{{ route('admin.chart-of-accounts.destroy', $account) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this account?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                                            <i class="bi bi-trash me-1"></i> Delete
                                                        </button>
                                                    </form>
                                                @endif
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
@endif
@endsection

