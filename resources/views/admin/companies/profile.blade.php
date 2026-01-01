@extends('admin.layout')

@section('title', 'Company Profile')

@section('content')
<div class="mb-4 mb-md-12">
    <div class="d-flex justify-content-between align-items-center mb-4 mb-md-12">
        <h2 class="h4 mb-0">Company Profile</h2>
        <a href="{{ route('admin.dashboard') }}" onclick="if(typeof window.loadPageViaAjax === 'function') { event.preventDefault(); window.loadPageViaAjax('{{ route('admin.dashboard') }}'); }" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-building me-2"></i>Company Information</h5>
        </div>
        <div class="card-body">
            <form id="companyProfileForm" method="POST" action="{{ route('admin.companies.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $company->name) }}" 
                            class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Company Logo</label>
                        @php
                            $logoUrl = $company->getLogoUrl();
                        @endphp
                        @if($logoUrl)
                            <div class="mb-3">
                                <img src="{{ $logoUrl }}" alt="Company Logo" 
                                    class="img-thumbnail" style="max-height: 120px;">
                                <p class="text-muted small mt-2">Current logo</p>
                            </div>
                        @endif
                        <input type="file" name="logo" accept="image/*" 
                            class="form-control @error('logo') is-invalid @enderror">
                        <small class="text-muted">Max file size: 5MB. Supported formats: JPG, PNG, GIF</small>
                        @error('logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Favicon</label>
                        @php
                            $faviconUrl = $company->getFaviconUrl();
                        @endphp
                        @if($company->favicon)
                            <div class="mb-3">
                                <img src="{{ $faviconUrl }}" alt="Company Favicon" 
                                    class="img-thumbnail" style="max-height: 32px; width: 32px;">
                                <p class="text-muted small mt-2">Current favicon</p>
                            </div>
                        @endif
                        <input type="file" name="favicon" accept="image/*" 
                            class="form-control @error('favicon') is-invalid @enderror">
                        <small class="text-muted">Max file size: 1MB. Recommended size: 32x32px. If not provided, a default favicon will be generated using the first letter of the company name.</small>
                        @error('favicon')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" name="address" value="{{ old('address', $company->address) }}" 
                            class="form-control @error('address') is-invalid @enderror">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">City</label>
                        <input type="text" name="city" value="{{ old('city', $company->city) }}" 
                            class="form-control @error('city') is-invalid @enderror">
                        @error('city')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">State / Province</label>
                        <input type="text" name="state" value="{{ old('state', $company->state) }}" 
                            class="form-control @error('state') is-invalid @enderror">
                        @error('state')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Country</label>
                        <input type="text" name="country" value="{{ old('country', $company->country) }}" 
                            class="form-control @error('country') is-invalid @enderror">
                        @error('country')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">ZIP / Postal Code</label>
                        <input type="text" name="zip" value="{{ old('zip', $company->zip) }}" 
                            class="form-control @error('zip') is-invalid @enderror">
                        @error('zip')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" value="{{ old('email', $company->email) }}" 
                            class="form-control @error('email') is-invalid @enderror">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" 
                            class="form-control @error('phone') is-invalid @enderror">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Website</label>
                        <input type="url" name="website" value="{{ old('website', $company->website) }}" 
                            class="form-control @error('website') is-invalid @enderror" 
                            placeholder="https://example.com">
                        @error('website')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Tax Number</label>
                        <input type="text" name="tax_number" value="{{ old('tax_number', $company->tax_number) }}" 
                            class="form-control @error('tax_number') is-invalid @enderror">
                        @error('tax_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <button type="submit" id="profile-submit-btn" class="btn btn-primary">
                        <i class="bi bi-check-circle me-2"></i><span id="profile-submit-text">Update Profile</span>
                    </button>
                    <a href="{{ route('admin.dashboard') }}" onclick="if(typeof window.loadPageViaAjax === 'function') { event.preventDefault(); window.loadPageViaAjax('{{ route('admin.dashboard') }}'); }" class="btn btn-outline-secondary ms-2">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('companyProfileForm');
    const submitBtn = document.getElementById('profile-submit-btn');
    const submitText = document.getElementById('profile-submit-text');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Hide previous errors
            document.querySelectorAll('.invalid-feedback').forEach(el => {
                el.style.display = 'none';
            });
            document.querySelectorAll('.is-invalid').forEach(el => {
                el.classList.remove('is-invalid');
            });
            
            const formData = new FormData(form);
            const originalText = submitText.textContent;
            
            submitBtn.disabled = true;
            submitText.textContent = 'Updating...';
            
            fetch('{{ route("admin.companies.profile.update") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => {
                        throw err;
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show success notification
                    if (typeof showNotification === 'function') {
                        showNotification(data.message, 'success');
                    } else {
                        alert(data.message);
                    }
                    
                    // Update logo/favicon preview if they were changed
                    if (formData.has('logo')) {
                        // Reload logo preview (would need to fetch new URL from server)
                        const logoInput = form.querySelector('input[name="logo"]');
                        if (logoInput && logoInput.files.length > 0) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const logoPreview = form.querySelector('img[alt="Company Logo"]');
                                if (logoPreview) {
                                    logoPreview.src = e.target.result;
                                }
                            };
                            reader.readAsDataURL(logoInput.files[0]);
                        }
                    }
                    
                    if (formData.has('favicon')) {
                        const faviconInput = form.querySelector('input[name="favicon"]');
                        if (faviconInput && faviconInput.files.length > 0) {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const faviconPreview = form.querySelector('img[alt="Company Favicon"]');
                                if (faviconPreview) {
                                    faviconPreview.src = e.target.result;
                                }
                            };
                            reader.readAsDataURL(faviconInput.files[0]);
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                if (error.errors) {
                    // Display validation errors
                    Object.keys(error.errors).forEach(field => {
                        const input = form.querySelector(`[name="${field}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            const errorDiv = input.nextElementSibling;
                            if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                                errorDiv.textContent = error.errors[field][0];
                                errorDiv.style.display = 'block';
                            }
                        }
                    });
                }
                
                // Show error notification
                const errorMessage = error.message || 'An error occurred while updating the profile.';
                if (typeof showNotification === 'function') {
                    showNotification(errorMessage, 'error');
                } else {
                    alert(errorMessage);
                }
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitText.textContent = originalText;
            });
        });
    }
});
</script>
@endpush
@endsection

