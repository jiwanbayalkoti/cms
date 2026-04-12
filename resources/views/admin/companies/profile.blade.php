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
                <input type="hidden" name="clear_logo" id="clear_logo" value="0">
                <input type="hidden" name="clear_favicon" id="clear_favicon" value="0">

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
                        <label class="form-label fw-semibold">PAN No. <span class="text-muted fw-normal">(प्यान नं. / tax ID)</span></label>
                        <input type="text" name="tax_number" value="{{ old('tax_number', $company->tax_number) }}"
                            class="form-control @error('tax_number') is-invalid @enderror"
                            placeholder="e.g. 606961234" autocomplete="off">
                        <small class="text-muted">Letterhead र कागजातमा माथि दायाँ देखाइन्छ।</small>
                        @error('tax_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Company Logo</label>
                        @php
                            $logoUrl = $company->getLogoUrl();
                        @endphp
                        <div class="mb-3 {{ $logoUrl ? '' : 'd-none' }}" id="profile-logo-preview-wrap">
                            <img src="{{ $logoUrl ?: '' }}" alt="Company Logo" id="profile-logo-preview"
                                class="img-thumbnail" style="max-height: 120px;">
                            <p class="text-muted small mt-2">Current logo</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <input type="file" name="logo" id="profile-logo-input" accept="image/*" 
                                class="form-control @error('logo') is-invalid @enderror" style="max-width: 520px;">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-logo-file-btn">
                                <i class="bi bi-x-circle me-1"></i>Clear file
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm {{ $company->logo ? '' : 'd-none' }}" id="remove-logo-btn">
                                <i class="bi bi-trash me-1"></i>Remove current logo
                            </button>
                        </div>
                        <small id="logo-clear-note" class="text-warning d-none mt-1 d-inline-block">Current logo will be removed after save.</small>
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
                        <div class="mb-3 {{ $company->favicon ? '' : 'd-none' }}" id="profile-favicon-preview-wrap">
                            <img src="{{ $company->favicon ? $faviconUrl : '' }}" alt="Company Favicon" id="profile-favicon-preview"
                                class="img-thumbnail" style="max-height: 32px; width: 32px;">
                            <p class="text-muted small mt-2">Current favicon</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <input type="file" name="favicon" id="profile-favicon-input" accept="image/*" 
                                class="form-control @error('favicon') is-invalid @enderror" style="max-width: 520px;">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-favicon-file-btn">
                                <i class="bi bi-x-circle me-1"></i>Clear file
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm {{ $company->favicon ? '' : 'd-none' }}" id="remove-favicon-btn">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Reset to default
                            </button>
                        </div>
                        <small id="favicon-clear-note" class="text-warning d-none mt-1 d-inline-block">Current favicon will be reset to default after save.</small>
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
    const logoInput = document.getElementById('profile-logo-input');
    const faviconInput = document.getElementById('profile-favicon-input');
    const clearLogoField = document.getElementById('clear_logo');
    const clearFaviconField = document.getElementById('clear_favicon');
    const logoNote = document.getElementById('logo-clear-note');
    const faviconNote = document.getElementById('favicon-clear-note');
    const clearLogoFileBtn = document.getElementById('clear-logo-file-btn');
    const clearFaviconFileBtn = document.getElementById('clear-favicon-file-btn');
    const removeLogoBtn = document.getElementById('remove-logo-btn');
    const removeFaviconBtn = document.getElementById('remove-favicon-btn');
    const logoPreviewWrap = document.getElementById('profile-logo-preview-wrap');
    const faviconPreviewWrap = document.getElementById('profile-favicon-preview-wrap');
    const logoPreviewImg = document.getElementById('profile-logo-preview');
    const faviconPreviewImg = document.getElementById('profile-favicon-preview');

    function notifyUser(message, type) {
        if (typeof showNotification === 'function') {
            showNotification(message, type);
            return;
        }

        const div = document.createElement('div');
        const isError = type === 'error';
        div.className = `position-fixed top-0 end-0 m-3 alert ${isError ? 'alert-danger' : 'alert-success'} shadow`;
        div.style.zIndex = '1080';
        div.textContent = message;
        document.body.appendChild(div);
        setTimeout(() => div.remove(), 3000);
    }

    function applyCompanyProfileData(company) {
        if (!company) return;

        const nameInput = form.querySelector('input[name="name"]');
        if (nameInput && typeof company.name === 'string') {
            nameInput.value = company.name;
        }

        const panInput = form.querySelector('input[name="tax_number"]');
        if (panInput && company.tax_number !== undefined && company.tax_number !== null) {
            panInput.value = company.tax_number;
        }

        if (logoPreviewWrap && logoPreviewImg) {
            if (company.logo_url) {
                logoPreviewImg.src = company.logo_url;
                logoPreviewWrap.classList.remove('d-none');
            } else {
                logoPreviewImg.src = '';
                logoPreviewWrap.classList.add('d-none');
            }
        }

        if (faviconPreviewWrap && faviconPreviewImg) {
            if (company.favicon_url) {
                faviconPreviewImg.src = company.favicon_url;
                faviconPreviewWrap.classList.remove('d-none');
            } else {
                faviconPreviewImg.src = '';
                faviconPreviewWrap.classList.add('d-none');
            }
        }

        if (removeLogoBtn) {
            removeLogoBtn.classList.toggle('d-none', !company.has_stored_logo);
        }
        if (removeFaviconBtn) {
            removeFaviconBtn.classList.toggle('d-none', !company.has_stored_favicon);
        }

        if (logoInput) logoInput.value = '';
        if (faviconInput) faviconInput.value = '';
        if (clearLogoField) clearLogoField.value = '0';
        if (clearFaviconField) clearFaviconField.value = '0';
        if (logoNote) logoNote.classList.add('d-none');
        if (faviconNote) faviconNote.classList.add('d-none');
    }

    if (clearLogoFileBtn && logoInput) {
        clearLogoFileBtn.addEventListener('click', function() {
            logoInput.value = '';
        });
    }

    if (clearFaviconFileBtn && faviconInput) {
        clearFaviconFileBtn.addEventListener('click', function() {
            faviconInput.value = '';
        });
    }

    if (removeLogoBtn && logoInput && clearLogoField) {
        removeLogoBtn.addEventListener('click', function() {
            logoInput.value = '';
            clearLogoField.value = '1';
            if (logoNote) logoNote.classList.remove('d-none');
            if (logoPreviewWrap && logoPreviewImg) {
                logoPreviewImg.src = '';
                logoPreviewWrap.classList.add('d-none');
            }
            removeLogoBtn.classList.add('d-none');
        });
        logoInput.addEventListener('change', function() {
            if (logoInput.files.length > 0) {
                clearLogoField.value = '0';
                if (logoNote) logoNote.classList.add('d-none');
                if (removeLogoBtn) removeLogoBtn.classList.remove('d-none');
            }
        });
    }

    if (removeFaviconBtn && faviconInput && clearFaviconField) {
        removeFaviconBtn.addEventListener('click', function() {
            faviconInput.value = '';
            clearFaviconField.value = '1';
            if (faviconNote) faviconNote.classList.remove('d-none');
            if (faviconPreviewWrap && faviconPreviewImg) {
                faviconPreviewImg.src = '';
                faviconPreviewWrap.classList.add('d-none');
            }
        });
        faviconInput.addEventListener('change', function() {
            if (faviconInput.files.length > 0) {
                clearFaviconField.value = '0';
                if (faviconNote) faviconNote.classList.add('d-none');
            }
        });
    }
    
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
                    notifyUser(data.message, 'success');
                    applyCompanyProfileData(data.company || null);
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
                            const errorDiv = input.closest('.col-md-12, .col-md-6')?.querySelector('.invalid-feedback');
                            if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                                errorDiv.textContent = error.errors[field][0];
                                errorDiv.style.display = 'block';
                            }
                        }
                    });
                }
                
                // Show error notification
                const errorMessage = error.message || 'An error occurred while updating the profile.';
                notifyUser(errorMessage, 'error');
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

