@extends('admin.layout')

@section('title', 'Add Supplier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add Supplier</h1>
    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

<div class="card">
            <div class="card-header">
                <strong>Supplier Details</strong>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.suppliers.store') }}" method="POST" id="supplierForm" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name *</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact</label>
                            <input type="text" name="contact" class="form-control" value="{{ old('contact') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <textarea name="address" rows="3" class="form-control">{{ old('address') }}</textarea>
                    </div>

                    <hr class="my-4">
                    <h5 class="mb-3">Bank Details</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bank Name</label>
                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Holder Name</label>
                            <input type="text" name="account_holder_name" class="form-control" value="{{ old('account_holder_name') }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Number</label>
                            <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Branch Name</label>
                            <input type="text" name="branch_name" class="form-control" value="{{ old('branch_name') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Branch Address</label>
                        <input type="text" name="branch_address" class="form-control" value="{{ old('branch_address') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">QR Code Image</label>
                        <input type="file" name="qr_code_image" class="form-control" accept="image/*" onchange="previewQRImage(this)">
                        <small class="text-muted">Upload a QR code image (JPEG, PNG, JPG, GIF, SVG - Max: 2MB)</small>
                        <div id="qr-preview" class="mt-2" style="display: none;">
                            <img id="qr-preview-img" src="" alt="QR Code Preview" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">Save Supplier</button>
                    </div>
                </form>
            </div>
        </div>
</div>

@push('scripts')
<script>
function previewQRImage(input) {
    const previewDiv = document.getElementById('qr-preview');
    const previewImg = document.getElementById('qr-preview-img');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewDiv.style.display = 'block';
        }
        
        reader.readAsDataURL(input.files[0]);
    } else {
        previewDiv.style.display = 'none';
    }
}
</script>
@endpush
@endsection


