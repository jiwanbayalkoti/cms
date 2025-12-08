@extends('admin.layout')

@section('title', 'Edit Bank/Cash Account')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Edit Bank/Cash Account</h1>
        <p class="text-muted mb-0">Update account information</p>
    </div>
    <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.bank-accounts.update', $bankAccount) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Account Name <span class="text-danger">*</span></label>
                    <input type="text" name="account_name" class="form-control @error('account_name') is-invalid @enderror" 
                           value="{{ old('account_name', $bankAccount->account_name) }}" required>
                    @error('account_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Account Type <span class="text-danger">*</span></label>
                    <select name="account_type" id="account_type" class="form-select @error('account_type') is-invalid @enderror" required>
                        <option value="bank" {{ old('account_type', $bankAccount->account_type) === 'bank' ? 'selected' : '' }}>Bank Account</option>
                        <option value="cash" {{ old('account_type', $bankAccount->account_type) === 'cash' ? 'selected' : '' }}>Cash Account</option>
                    </select>
                    @error('account_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div id="bank_fields" style="display: {{ old('account_type', $bankAccount->account_type) === 'bank' ? 'block' : 'none' }};">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                        <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" 
                               value="{{ old('bank_name', $bankAccount->bank_name) }}" required>
                        @error('bank_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_number" class="form-control" value="{{ old('account_number', $bankAccount->account_number) }}">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Branch Name</label>
                        <input type="text" name="branch_name" class="form-control" value="{{ old('branch_name', $bankAccount->branch_name) }}">
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label">SWIFT Code</label>
                        <input type="text" name="swift_code" class="form-control" value="{{ old('swift_code', $bankAccount->swift_code) }}">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Branch Address</label>
                    <textarea name="branch_address" rows="2" class="form-control">{{ old('branch_address', $bankAccount->branch_address) }}</textarea>
                </div>
            </div>
            
            <div id="cash_fields" style="display: {{ old('account_type', $bankAccount->account_type) === 'cash' ? 'block' : 'none' }};">
                <div class="mb-3">
                    <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                    <input type="text" name="bank_name" class="form-control @error('bank_name') is-invalid @enderror" 
                           value="{{ old('bank_name', $bankAccount->bank_name) }}" required>
                    @error('bank_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Chart of Account</label>
                    <select name="chart_of_account_id" class="form-select">
                        <option value="">None</option>
                        @foreach($cashAccounts as $account)
                            <option value="{{ $account->id }}" {{ old('chart_of_account_id', $bankAccount->chart_of_account_id) == $account->id ? 'selected' : '' }}>
                                {{ $account->account_code }} - {{ $account->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Currency</label>
                    <select name="currency" class="form-select">
                        <option value="NPR" {{ old('currency', $bankAccount->currency) === 'NPR' ? 'selected' : '' }}>NPR</option>
                        <option value="USD" {{ old('currency', $bankAccount->currency) === 'USD' ? 'selected' : '' }}>USD</option>
                        <option value="EUR" {{ old('currency', $bankAccount->currency) === 'EUR' ? 'selected' : '' }}>EUR</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Opening Balance</label>
                    <input type="number" name="opening_balance" step="0.01" class="form-control" 
                           value="{{ old('opening_balance', $bankAccount->opening_balance) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Opening Balance Date</label>
                    <input type="date" name="opening_balance_date" class="form-control" 
                           value="{{ old('opening_balance_date', $bankAccount->opening_balance_date?->format('Y-m-d')) }}">
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" rows="3" class="form-control">{{ old('notes', $bankAccount->notes) }}</textarea>
            </div>
            
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" 
                       {{ old('is_active', $bankAccount->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active</label>
            </div>
            
            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Account</button>
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
    } else if (accountType === 'cash') {
        bankFields.style.display = 'none';
        cashFields.style.display = 'block';
    }
});
</script>
@endsection

