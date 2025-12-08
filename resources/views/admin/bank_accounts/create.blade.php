@extends('admin.layout')

@section('title', 'Create Bank/Cash Account')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Create Bank/Cash Account</h1>
        <p class="text-muted mb-0">Add a new bank or cash account</p>
    </div>
    <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.bank-accounts.store') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Account Name <span class="text-danger">*</span></label>
                    <input type="text" name="account_name" class="form-control @error('account_name') is-invalid @enderror" 
                           value="{{ old('account_name') }}" required placeholder="e.g., Nabil Bank - Main Account">
                    @error('account_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Account Type <span class="text-danger">*</span></label>
                    <select name="account_type" id="account_type" class="form-select @error('account_type') is-invalid @enderror" required>
                        <option value="">Select Type</option>
                        <option value="bank" {{ old('account_type') === 'bank' ? 'selected' : '' }}>Bank Account</option>
                        <option value="cash" {{ old('account_type') === 'cash' ? 'selected' : '' }}>Cash Account</option>
                    </select>
                    @error('account_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div id="bank_fields" style="display: {{ old('account_type') === 'bank' ? 'block' : 'none' }};">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" 
                               value="{{ old('bank_name') }}" placeholder="e.g., Nabil Bank">
                        @error('bank_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Branch Name</label>
                        <input type="text" name="branch_name" class="form-control" value="{{ old('branch_name') }}">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SWIFT Code</label>
                        <input type="text" name="swift_code" class="form-control" value="{{ old('swift_code') }}">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Branch Address</label>
                    <textarea name="branch_address" rows="2" class="form-control">{{ old('branch_address') }}</textarea>
                </div>
            </div>
            
            <div id="cash_fields" style="display: {{ old('account_type') === 'cash' ? 'block' : 'none' }};">
                <div class="mb-3">
                    <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                    <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" 
                           value="{{ old('bank_name', 'Cash in Hand') }}" placeholder="e.g., Cash in Hand">
                    @error('bank_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Chart of Account</label>
                    <select name="chart_of_account_id" class="form-select">
                        <option value="">Auto-select based on type</option>
                        @foreach($cashAccounts as $account)
                            <option value="{{ $account->id }}" {{ old('chart_of_account_id') == $account->id ? 'selected' : '' }}>
                                {{ $account->account_code }} - {{ $account->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Currency</label>
                    <select name="currency" class="form-select">
                        <option value="NPR" {{ old('currency', 'NPR') === 'NPR' ? 'selected' : '' }}>NPR</option>
                        <option value="USD" {{ old('currency') === 'USD' ? 'selected' : '' }}>USD</option>
                        <option value="EUR" {{ old('currency') === 'EUR' ? 'selected' : '' }}>EUR</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Opening Balance</label>
                    <input type="number" name="opening_balance" step="0.01" class="form-control" 
                           value="{{ old('opening_balance', 0) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Opening Balance Date</label>
                    <input type="date" name="opening_balance_date" class="form-control" 
                           value="{{ old('opening_balance_date', date('Y-m-d')) }}">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
            </div>
            
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" 
                       {{ old('is_active', true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            
            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('account_type').addEventListener('change', function() {
    const accountType = this.value;
    const bankFields = document.getElementById('bank_fields');
    const cashFields = document.getElementById('cash_fields');
    
    if (accountType === 'bank') {
        bankFields.style.display = 'block';
        cashFields.style.display = 'none';
        document.querySelector('[name="bank_name"]').required = true;
    } else if (accountType === 'cash') {
        bankFields.style.display = 'none';
        cashFields.style.display = 'block';
        document.querySelector('[name="bank_name"]').required = true;
    } else {
        bankFields.style.display = 'none';
        cashFields.style.display = 'none';
    }
});
</script>
@endsection

