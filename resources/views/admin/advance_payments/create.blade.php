@extends('admin.layout')

@section('title', 'Add Advance Payment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
<div>
        <h1 class="h3 mb-0">Add Advance Payment</h1>
        <p class="text-muted mb-0">Record an advance payment for vehicle rent or material</p>
    </div>
    <a href="{{ route('admin.advance-payments.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.advance-payments.store') }}" method="POST" id="advancePaymentForm">
            @csrf
            
            <div class="row mb-4">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Payment Type <span class="text-danger">*</span></label>
                    <select name="payment_type" id="payment_type" class="form-select @error('payment_type') is-invalid @enderror" required>
                        <option value="">Select Payment Type</option>
                        @foreach($paymentTypes as $type)
                            @php
                                // Always use code, never ID - ensure it's a string
                                $typeCode = !empty($type->code) ? $type->code : strtolower(str_replace(' ', '_', $type->name));
                            @endphp
                            <option value="{{ $typeCode }}" {{ old('payment_type') == $typeCode ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('payment_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Project</label>
                    <select name="project_id" class="form-select">
                        <option value="">None</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label class="form-label">Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" id="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('supplier_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Payment Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                            <input type="number" name="amount" id="amount" step="0.01" min="0.01" 
                                   class="form-control @error('amount') is-invalid @enderror" 
                                   value="{{ old('amount') }}" placeholder="0.00" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Amount to be paid in advance</small>
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control @error('payment_date') is-invalid @enderror" 
                                   value="{{ old('payment_date', date('Y-m-d')) }}" required>
                            @error('payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bank Account</label>
                            <select name="bank_account_id" class="form-select">
                                <option value="">None</option>
                                @foreach($bankAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('bank_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->account_name }} ({{ $account->account_type }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="">Select Method</option>
                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                <option value="cheque" {{ old('payment_method') == 'cheque' ? 'selected' : '' }}>Cheque</option>
                                <option value="online_payment" {{ old('payment_method') == 'online_payment' ? 'selected' : '' }}>Online Payment</option>
                                <option value="other" {{ old('payment_method') == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Transaction Reference</label>
                            <input type="text" name="transaction_reference" class="form-control" value="{{ old('transaction_reference') }}" 
                                   placeholder="Transaction ID, Cheque Number, etc.">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" rows="3" class="form-control" placeholder="Additional notes">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end">
                <a href="{{ route('admin.advance-payments.index') }}" class="btn btn-secondary me-2">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Advance Payment</button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePaymentFields() {
    // Payment type selection - no additional fields needed
}
</script>
@endsection
