@extends('admin.layout')

@section('title', 'Edit Chart of Account')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Edit Chart of Account</h1>
        <p class="text-muted mb-0">Update account information</p>
    </div>
    <a href="{{ route('admin.chart-of-accounts.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.chart-of-accounts.update', $chartOfAccount) }}" method="POST">
            @csrf
            @method('PUT')
            
            @if($chartOfAccount->is_system)
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>This is a system account. Some fields may be restricted.
                </div>
            @endif
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Account Code <span class="text-danger">*</span></label>
                    <input type="text" name="account_code" class="form-control @error('account_code') is-invalid @enderror" 
                           value="{{ old('account_code', $chartOfAccount->account_code) }}" required 
                           {{ $chartOfAccount->is_system ? 'readonly' : '' }}>
                    @error('account_code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Account Name <span class="text-danger">*</span></label>
                    <input type="text" name="account_name" class="form-control @error('account_name') is-invalid @enderror" 
                           value="{{ old('account_name', $chartOfAccount->account_name) }}" required>
                    @error('account_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Account Type <span class="text-danger">*</span></label>
                    <select name="account_type" class="form-select @error('account_type') is-invalid @enderror" required
                            {{ $chartOfAccount->is_system ? 'disabled' : '' }}>
                        <option value="">Select Type</option>
                        <option value="asset" {{ old('account_type', $chartOfAccount->account_type) === 'asset' ? 'selected' : '' }}>Asset</option>
                        <option value="liability" {{ old('account_type', $chartOfAccount->account_type) === 'liability' ? 'selected' : '' }}>Liability</option>
                        <option value="equity" {{ old('account_type', $chartOfAccount->account_type) === 'equity' ? 'selected' : '' }}>Equity</option>
                        <option value="revenue" {{ old('account_type', $chartOfAccount->account_type) === 'revenue' ? 'selected' : '' }}>Revenue</option>
                        <option value="expense" {{ old('account_type', $chartOfAccount->account_type) === 'expense' ? 'selected' : '' }}>Expense</option>
                    </select>
                    @if($chartOfAccount->is_system)
                        <input type="hidden" name="account_type" value="{{ $chartOfAccount->account_type }}">
                    @endif
                    @error('account_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Parent Account</label>
                    <select name="parent_account_id" class="form-select @error('parent_account_id') is-invalid @enderror">
                        <option value="">None (Top Level)</option>
                        @foreach($parentAccounts as $parent)
                            @if($parent->id !== $chartOfAccount->id)
                                <option value="{{ $parent->id }}" {{ old('parent_account_id', $chartOfAccount->parent_account_id) == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->account_code }} - {{ $parent->account_name }}
                                </option>
                            @endif
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
                           value="{{ old('account_category', $chartOfAccount->account_category) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Balance Type <span class="text-danger">*</span></label>
                    <select name="balance_type" class="form-select @error('balance_type') is-invalid @enderror" required>
                        <option value="debit" {{ old('balance_type', $chartOfAccount->balance_type) === 'debit' ? 'selected' : '' }}>Debit</option>
                        <option value="credit" {{ old('balance_type', $chartOfAccount->balance_type) === 'credit' ? 'selected' : '' }}>Credit</option>
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
                           value="{{ old('opening_balance', $chartOfAccount->opening_balance) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Display Order</label>
                    <input type="number" name="display_order" class="form-control" 
                           value="{{ old('display_order', $chartOfAccount->display_order) }}">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $chartOfAccount->description) }}</textarea>
            </div>
            
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" 
                       {{ old('is_active', $chartOfAccount->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            
            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.chart-of-accounts.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Account</button>
            </div>
        </form>
    </div>
</div>
@endsection

