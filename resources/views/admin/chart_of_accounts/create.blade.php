@extends('admin.layout')

@section('title', 'Create Chart of Account')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Create Chart of Account</h1>
        <p class="text-muted mb-0">Add a new account to your chart of accounts</p>
    </div>
    <a href="{{ route('admin.chart-of-accounts.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.chart-of-accounts.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Account Code <span class="text-danger">*</span></label>
                    <input type="text" name="account_code" class="form-control @error('account_code') is-invalid @enderror" 
                           value="{{ old('account_code') }}" required placeholder="e.g., 1001, 2001">
                    @error('account_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Account Name <span class="text-danger">*</span></label>
                    <input type="text" name="account_name" class="form-control @error('account_name') is-invalid @enderror" 
                           value="{{ old('account_name') }}" required>
                    @error('account_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Account Type <span class="text-danger">*</span></label>
                    <select name="account_type" class="form-select @error('account_type') is-invalid @enderror" required>
                        <option value="">Select Type</option>
                        <option value="asset" {{ old('account_type') === 'asset' ? 'selected' : '' }}>Asset</option>
                        <option value="liability" {{ old('account_type') === 'liability' ? 'selected' : '' }}>Liability</option>
                        <option value="equity" {{ old('account_type') === 'equity' ? 'selected' : '' }}>Equity</option>
                        <option value="revenue" {{ old('account_type') === 'revenue' ? 'selected' : '' }}>Revenue</option>
                        <option value="expense" {{ old('account_type') === 'expense' ? 'selected' : '' }}>Expense</option>
                    </select>
                    @error('account_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Parent Account</label>
                    <select name="parent_account_id" class="form-select @error('parent_account_id') is-invalid @enderror">
                        <option value="">None (Top Level)</option>
                        @foreach($parentAccounts as $parent)
                            <option value="{{ $parent->id }}" {{ old('parent_account_id') == $parent->id ? 'selected' : '' }}>
                                {{ $parent->account_code }} - {{ $parent->account_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('parent_account_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Account Category</label>
                    <input type="text" name="account_category" class="form-control" 
                           value="{{ old('account_category') }}" placeholder="e.g., current_asset, operating_expense">
                    <small class="form-text text-muted">Optional: Category for grouping accounts</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Balance Type <span class="text-danger">*</span></label>
                    <select name="balance_type" class="form-select @error('balance_type') is-invalid @enderror" required>
                        <option value="debit" {{ old('balance_type', 'debit') === 'debit' ? 'selected' : '' }}>Debit</option>
                        <option value="credit" {{ old('balance_type') === 'credit' ? 'selected' : '' }}>Credit</option>
                    </select>
                    @error('balance_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Opening Balance</label>
                    <input type="number" name="opening_balance" step="0.01" class="form-control" 
                           value="{{ old('opening_balance', 0) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-control" 
                           value="{{ old('display_order', 0) }}">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
            </div>
            
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" 
                       {{ old('is_active', true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            
            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.chart-of-accounts.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </div>
        </form>
    </div>
</div>
@endsection

