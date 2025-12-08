@extends('admin.layout')

@section('title', 'Chart of Account Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Chart of Account Details</h1>
        <p class="text-muted mb-0">{{ $chartOfAccount->account_code }} - {{ $chartOfAccount->account_name }}</p>
    </div>
    <div>
        <a href="{{ route('admin.chart-of-accounts.edit', $chartOfAccount) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.chart-of-accounts.index') }}" class="btn btn-outline-secondary">Back to List</a>
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
                        <th width="200">Account Code:</th>
                        <td><span class="badge bg-secondary">{{ $chartOfAccount->account_code }}</span></td>
                    </tr>
                    <tr>
                        <th>Account Name:</th>
                        <td><strong>{{ $chartOfAccount->account_name }}</strong></td>
                    </tr>
                    <tr>
                        <th>Account Type:</th>
                        <td>
                            <span class="badge bg-{{ $chartOfAccount->account_type === 'asset' ? 'primary' : ($chartOfAccount->account_type === 'liability' ? 'warning' : ($chartOfAccount->account_type === 'equity' ? 'info' : ($chartOfAccount->account_type === 'revenue' ? 'success' : 'danger'))) }}">
                                {{ ucfirst($chartOfAccount->account_type) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Account Category:</th>
                        <td>{{ $chartOfAccount->account_category ? ucfirst(str_replace('_', ' ', $chartOfAccount->account_category)) : '—' }}</td>
                    </tr>
                    <tr>
                        <th>Parent Account:</th>
                        <td>
                            @if($chartOfAccount->parentAccount)
                                <a href="{{ route('admin.chart-of-accounts.show', $chartOfAccount->parentAccount) }}">
                                    {{ $chartOfAccount->parentAccount->account_code }} - {{ $chartOfAccount->parentAccount->account_name }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Level:</th>
                        <td>{{ $chartOfAccount->level }}</td>
                    </tr>
                    <tr>
                        <th>Balance Type:</th>
                        <td>
                            <span class="badge bg-{{ $chartOfAccount->balance_type === 'debit' ? 'success' : 'danger' }}">
                                {{ ucfirst($chartOfAccount->balance_type) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Opening Balance:</th>
                        <td><strong>{{ number_format($chartOfAccount->opening_balance, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Current Balance:</th>
                        <td><strong class="text-primary">{{ number_format($chartOfAccount->current_balance, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            <span class="badge rounded-pill {{ $chartOfAccount->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $chartOfAccount->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @if($chartOfAccount->is_system)
                                <span class="badge bg-info ms-2">System Account</span>
                            @endif
                        </td>
                    </tr>
                    @if($chartOfAccount->description)
                        <tr>
                            <th>Description:</th>
                            <td>{{ $chartOfAccount->description }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
        
        @if($chartOfAccount->childAccounts->count() > 0)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Child Accounts ({{ $chartOfAccount->childAccounts->count() }})</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th class="text-end">Balance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($chartOfAccount->childAccounts as $child)
                                    <tr>
                                        <td><span class="badge bg-secondary">{{ $child->account_code }}</span></td>
                                        <td>{{ $child->account_name }}</td>
                                        <td><span class="badge bg-light text-dark">{{ ucfirst($child->account_type) }}</span></td>
                                        <td class="text-end">{{ number_format($child->current_balance, 2) }}</td>
                                        <td>
                                            <a href="{{ route('admin.chart-of-accounts.show', $child) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
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
                @if(!$chartOfAccount->is_system)
                    <form action="{{ route('admin.chart-of-accounts.destroy', $chartOfAccount) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this account?');" class="mb-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash me-1"></i> Delete Account
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

