@extends('admin.layout')

@section('title', 'Companies')

@section('content')
<div class="flex justify-between mb-4">
    <h1 class="text-2xl font-bold">Companies</h1>
    <button onclick="openCreateCompanyModal()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">New Company</button>
  </div>

  <div id="companiesContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($companies as $company)
      <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 p-6" data-company-id="{{ $company->id }}">
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $company->name }}</h3>
            @if($company->logo_url)
              <img src="{{ $company->logo_url }}" alt="{{ $company->name }} Logo" class="h-12 w-auto mt-2">
            @endif
          </div>
        </div>
        
        <div class="space-y-2 mb-4">
          @if($company->email)
            <div class="flex items-center text-sm text-gray-600">
              <i class="bi bi-envelope me-2"></i>
              <span class="truncate">{{ $company->email }}</span>
            </div>
          @endif
          
          @if($company->phone)
            <div class="flex items-center text-sm text-gray-600">
              <i class="bi bi-telephone me-2"></i>
              <span>{{ $company->phone }}</span>
            </div>
          @endif
          
          @if($company->address)
            <div class="flex items-start text-sm text-gray-600">
              <i class="bi bi-geo-alt me-2 mt-0.5"></i>
              <span class="flex-1">{{ Str::limit($company->address, 50) }}</span>
            </div>
          @endif
        </div>
        
        <div class="flex items-center justify-end gap-1 pt-4 border-t">
          <button onclick="openViewCompanyModal({{ $company->id }})" class="btn btn-sm btn-outline-primary" title="View">
            <i class="bi bi-eye"></i>
          </button>
          <button onclick="openEditCompanyModal({{ $company->id }})" class="btn btn-sm btn-outline-warning" title="Edit">
            <i class="bi bi-pencil"></i>
          </button>
          <button onclick="showDeleteConfirmation({{ $company->id }}, '{{ addslashes($company->name) }}')" class="btn btn-sm btn-outline-danger" title="Delete">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </div>
    @empty
      <div class="col-span-full bg-white rounded-lg shadow p-8 text-center">
        <p class="text-gray-500 text-lg">No companies found.</p>
        <button onclick="openCreateCompanyModal()" class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
          Create First Company
        </button>
      </div>
    @endforelse
  </div>
  
  <div class="mt-6">
    <x-pagination :paginator="$companies" wrapper-class="mt-4" />
  </div>

<!-- Create/Edit Company Modal -->
<div id="createCompanyModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="modal-title">Create New Company</h3>
            <button onclick="closeCreateCompanyModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="createCompanyForm" enctype="multipart/form-data" class="p-6">
            @csrf
            <input type="hidden" name="_method" id="form-method" value="POST">
            <input type="hidden" name="company_id" id="company-id" value="">
            <div id="form-errors" class="mb-4 hidden">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside text-sm" id="error-list"></ul>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="company-name" class="w-full border rounded px-3 py-2" required>
                <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Address</label>
                <input type="text" name="address" id="company-address" class="w-full border rounded px-3 py-2">
                <div class="field-error text-red-600 text-sm mt-1" data-field="address" style="display: none;"></div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Logo</label>
                <div id="logo-preview" class="mb-2 hidden">
                    <img id="logo-preview-img" src="" alt="Company Logo" class="h-16 rounded shadow">
                </div>
                <input type="file" name="logo" id="company-logo" accept="image/*" class="w-full border rounded px-3 py-2" onchange="previewLogo(this)">
                <div class="field-error text-red-600 text-sm mt-1" data-field="logo" style="display: none;"></div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Favicon</label>
                <div id="favicon-preview" class="mb-2 hidden">
                    <img id="favicon-preview-img" src="" alt="Company Favicon" class="h-8 w-8 rounded shadow">
                </div>
                <input type="file" name="favicon" id="company-favicon" accept="image/*" class="w-full border rounded px-3 py-2" onchange="previewFavicon(this)">
                <p class="text-gray-500 text-xs mt-1">Upload a favicon (32x32 recommended). If not provided, a default favicon will be generated.</p>
                <div class="field-error text-red-600 text-sm mt-1" data-field="favicon" style="display: none;"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" id="company-email" class="w-full border rounded px-3 py-2">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="email" style="display: none;"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input type="text" name="phone" id="company-phone" class="w-full border rounded px-3 py-2">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="phone" style="display: none;"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Website</label>
                    <input type="url" name="website" id="company-website" class="w-full border rounded px-3 py-2">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="website" style="display: none;"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Tax Number</label>
                    <input type="text" name="tax_number" id="company-tax-number" class="w-full border rounded px-3 py-2">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="tax_number" style="display: none;"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium mb-1">City</label>
                    <input type="text" name="city" id="company-city" class="w-full border rounded px-3 py-2">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="city" style="display: none;"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">State</label>
                    <input type="text" name="state" id="company-state" class="w-full border rounded px-3 py-2">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="state" style="display: none;"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Country</label>
                    <input type="text" name="country" id="company-country" class="w-full border rounded px-3 py-2">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="country" style="display: none;"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">ZIP</label>
                    <input type="text" name="zip" id="company-zip" class="w-full border rounded px-3 py-2">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="zip" style="display: none;"></div>
                </div>
            </div>

            <div class="flex space-x-2 mt-6">
                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition" id="submit-btn">
                    Save
                </button>
                <button type="button" onclick="closeCreateCompanyModal()" class="px-4 py-2 rounded border hover:bg-gray-50 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Company Modal -->
<div id="viewCompanyModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="view-modal-title">Company Details</h3>
            <button onclick="closeViewCompanyModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div id="view-company-content" class="p-6">
            <!-- Loading state -->
            <div id="view-loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                <p class="mt-2 text-gray-600">Loading company details...</p>
            </div>
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <!-- Icon -->
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            
            <!-- Title -->
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Company</h3>
            
            <!-- Message -->
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-company-name"></span>? This action cannot be undone.
            </p>
            
            <!-- Buttons -->
            <div class="flex space-x-3">
                <button onclick="closeDeleteConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <form id="delete-company-form" method="POST" class="flex-1">
                @csrf
                @method('DELETE')
                    <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                        Delete
                    </button>
              </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Use window object to avoid redeclaration errors when scripts are executed multiple times
if (typeof window.csrfToken === 'undefined') {
    window.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
}
// Use window.csrfToken directly or create a local reference without const
var csrfToken = window.csrfToken;
let currentCompanyId = null;
let deleteCompanyId = null;

function openCreateCompanyModal() {
    currentCompanyId = null;
    document.getElementById('modal-title').textContent = 'Create New Company';
    document.getElementById('form-method').value = 'POST';
    document.getElementById('company-id').value = '';
    document.getElementById('createCompanyModal').classList.remove('hidden');
    document.getElementById('createCompanyForm').reset();
    document.getElementById('form-errors').classList.add('hidden');
    document.getElementById('logo-preview').classList.add('hidden');
    document.getElementById('favicon-preview').classList.add('hidden');
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = false;
    submitBtn.textContent = 'Save';
}

function openEditCompanyModal(companyId) {
    currentCompanyId = companyId;
    document.getElementById('modal-title').textContent = 'Edit Company';
    document.getElementById('form-method').value = 'PUT';
    document.getElementById('company-id').value = companyId;
    
    // Fetch company data
    fetch(`/admin/companies/${companyId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.company) {
            const company = data.company;
            document.getElementById('company-name').value = company.name || '';
            document.getElementById('company-address').value = company.address || '';
            document.getElementById('company-email').value = company.email || '';
            document.getElementById('company-phone').value = company.phone || '';
            document.getElementById('company-website').value = company.website || '';
            document.getElementById('company-tax-number').value = company.tax_number || '';
            document.getElementById('company-city').value = company.city || '';
            document.getElementById('company-state').value = company.state || '';
            document.getElementById('company-country').value = company.country || '';
            document.getElementById('company-zip').value = company.zip || '';
            
            // Show logo preview if exists
            if (company.logo_url) {
                document.getElementById('logo-preview-img').src = company.logo_url;
                document.getElementById('logo-preview').classList.remove('hidden');
            } else {
                document.getElementById('logo-preview').classList.add('hidden');
            }
            
            // Show favicon preview if exists
            if (company.favicon_url) {
                document.getElementById('favicon-preview-img').src = company.favicon_url;
                document.getElementById('favicon-preview').classList.remove('hidden');
            } else {
                document.getElementById('favicon-preview').classList.add('hidden');
            }
            
            document.getElementById('createCompanyModal').classList.remove('hidden');
            document.getElementById('form-errors').classList.add('hidden');
            document.querySelectorAll('.field-error').forEach(el => {
                el.style.display = 'none';
                el.textContent = '';
            });
            const submitBtn = document.getElementById('submit-btn');
            submitBtn.disabled = false;
            submitBtn.textContent = 'Update';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Failed to load company data', 'error');
    });
}

function closeCreateCompanyModal() {
    document.getElementById('createCompanyModal').classList.add('hidden');
    document.getElementById('createCompanyForm').reset();
    document.getElementById('form-errors').classList.add('hidden');
    document.getElementById('logo-preview').classList.add('hidden');
    document.getElementById('favicon-preview').classList.add('hidden');
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    // Re-enable submit button
    const submitBtn = document.getElementById('submit-btn');
    submitBtn.disabled = false;
    submitBtn.textContent = 'Save';
    currentCompanyId = null;
}

function previewLogo(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logo-preview-img').src = e.target.result;
            document.getElementById('logo-preview').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewFavicon(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('favicon-preview-img').src = e.target.result;
            document.getElementById('favicon-preview').classList.remove('hidden');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

document.getElementById('createCompanyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submit-btn');
    const originalText = submitBtn.textContent;
    const isEdit = currentCompanyId !== null;
    const url = isEdit ? `/admin/companies/${currentCompanyId}` : '{{ route("admin.companies.store") }}';
    const method = isEdit ? 'POST' : 'POST';
    
    // Hide previous errors
    document.getElementById('form-errors').classList.add('hidden');
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    submitBtn.disabled = true;
    submitBtn.textContent = isEdit ? 'Updating...' : 'Saving...';
    
    fetch(url, {
        method: method,
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(html => {
                throw new Error('Server returned HTML instead of JSON');
            });
        }
    })
    .then(data => {
        if (data.success) {
            // Re-enable button before closing modal
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
            
            closeCreateCompanyModal();
            
            if (isEdit) {
                // Update existing row
                updateCompanyRow(data.company);
                showSuccessMessage('Company updated successfully!');
            } else {
                // Add new row to table
                addCompanyRow(data.company);
                showSuccessMessage('Company created successfully!');
            }
        } else {
            // Handle validation errors
            if (data.errors) {
                const errorList = document.getElementById('error-list');
                errorList.innerHTML = '';
                document.getElementById('form-errors').classList.remove('hidden');
                
                Object.keys(data.errors).forEach(field => {
                    const errorMsg = Array.isArray(data.errors[field]) ? data.errors[field][0] : data.errors[field];
                    const errorEl = document.querySelector(`.field-error[data-field="${field}"]`);
                    if (errorEl) {
                        errorEl.textContent = errorMsg;
                        errorEl.style.display = 'block';
                    }
                    const li = document.createElement('li');
                    li.textContent = errorMsg;
                    errorList.appendChild(li);
                });
            } else {
                showNotification(data.message || (isEdit ? 'Failed to update company' : 'Failed to create company'), 'error');
            }
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCreateCompanyModal();
    }
});

// Close modal when clicking outside
document.getElementById('createCompanyModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeCreateCompanyModal();
    }
});

// Function to show notification message
function showNotification(message, type = 'success') {
    // Create a temporary notification element
    const notificationDiv = document.createElement('div');
    notificationDiv.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-2xl z-50 transition-all duration-300 flex items-center gap-3 min-w-[300px] max-w-[500px]`;
    
    // Set colors based on type
    if (type === 'success') {
        notificationDiv.className += ' bg-green-500 text-white';
    } else if (type === 'error') {
        notificationDiv.className += ' bg-red-500 text-white';
    } else if (type === 'warning') {
        notificationDiv.className += ' bg-yellow-500 text-white';
    } else {
        notificationDiv.className += ' bg-blue-500 text-white';
    }
    
    // Add icon
    const iconSvg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    iconSvg.setAttribute('class', 'w-6 h-6 flex-shrink-0');
    iconSvg.setAttribute('fill', 'none');
    iconSvg.setAttribute('stroke', 'currentColor');
    iconSvg.setAttribute('viewBox', '0 0 24 24');
    
    const iconPath = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    if (type === 'success') {
        iconPath.setAttribute('stroke-linecap', 'round');
        iconPath.setAttribute('stroke-linejoin', 'round');
        iconPath.setAttribute('stroke-width', '2');
        iconPath.setAttribute('d', 'M5 13l4 4L19 7');
    } else if (type === 'error') {
        iconPath.setAttribute('stroke-linecap', 'round');
        iconPath.setAttribute('stroke-linejoin', 'round');
        iconPath.setAttribute('stroke-width', '2');
        iconPath.setAttribute('d', 'M6 18L18 6M6 6l12 12');
    } else {
        iconPath.setAttribute('stroke-linecap', 'round');
        iconPath.setAttribute('stroke-linejoin', 'round');
        iconPath.setAttribute('stroke-width', '2');
        iconPath.setAttribute('d', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z');
    }
    iconSvg.appendChild(iconPath);
    
    // Add message text
    const messageText = document.createElement('span');
    messageText.className = 'flex-1';
    messageText.textContent = message;
    
    // Add close button
    const closeBtn = document.createElement('button');
    closeBtn.className = 'ml-2 text-white hover:text-gray-200 transition-colors';
    closeBtn.innerHTML = `
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    `;
    closeBtn.onclick = () => {
        notificationDiv.style.opacity = '0';
        notificationDiv.style.transform = 'translateX(100%)';
        setTimeout(() => notificationDiv.remove(), 300);
    };
    
    notificationDiv.appendChild(iconSvg);
    notificationDiv.appendChild(messageText);
    notificationDiv.appendChild(closeBtn);
    
    // Initial state (hidden, slide from right)
    notificationDiv.style.opacity = '0';
    notificationDiv.style.transform = 'translateX(100%)';
    document.body.appendChild(notificationDiv);
    
    // Animate in
    setTimeout(() => {
        notificationDiv.style.opacity = '1';
        notificationDiv.style.transform = 'translateX(0)';
    }, 10);
    
    // Auto remove after 5 seconds (or 3 seconds for success)
    const autoRemoveTime = type === 'success' ? 3000 : 5000;
    setTimeout(() => {
        notificationDiv.style.opacity = '0';
        notificationDiv.style.transform = 'translateX(100%)';
        setTimeout(() => {
            notificationDiv.remove();
        }, 300);
    }, autoRemoveTime);
}

// Function to show success message (backward compatibility)
function showSuccessMessage(message) {
    showNotification(message, 'success');
}

// Function to add new company row to table
function addCompanyRow(company) {
    const container = document.getElementById('companiesContainer');
    
    // Check if container is empty
    const emptyDiv = container.querySelector('.col-span-full');
    if (emptyDiv) {
        container.innerHTML = '';
    }
    
    const card = document.createElement('div');
    card.className = 'bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200 p-6';
    card.setAttribute('data-company-id', company.id);
    
    const logoHtml = company.logo_url ? `<img src="${escapeHtml(company.logo_url)}" alt="${escapeHtml(company.name || '')} Logo" class="h-12 w-auto mt-2">` : '';
    const emailHtml = company.email ? `
      <div class="flex items-center text-sm text-gray-600">
        <i class="bi bi-envelope me-2"></i>
        <span class="truncate">${escapeHtml(company.email)}</span>
      </div>
    ` : '';
    const phoneHtml = company.phone ? `
      <div class="flex items-center text-sm text-gray-600">
        <i class="bi bi-telephone me-2"></i>
        <span>${escapeHtml(company.phone)}</span>
      </div>
    ` : '';
    const addressHtml = company.address ? `
      <div class="flex items-start text-sm text-gray-600">
        <i class="bi bi-geo-alt me-2 mt-0.5"></i>
        <span class="flex-1">${escapeHtml(company.address.length > 50 ? company.address.substring(0, 50) + '...' : company.address)}</span>
      </div>
    ` : '';
    
    card.innerHTML = `
        <div class="flex items-start justify-between mb-4">
          <div class="flex-1">
            <h3 class="text-lg font-semibold text-gray-900 mb-1">${escapeHtml(company.name || '')}</h3>
            ${logoHtml}
          </div>
        </div>
        
        <div class="space-y-2 mb-4">
          ${emailHtml}
          ${phoneHtml}
          ${addressHtml}
        </div>
        
        <div class="flex items-center justify-end gap-1 pt-4 border-t">
          <button onclick="openViewCompanyModal(${company.id})" class="btn btn-sm btn-outline-primary" title="View">
            <i class="bi bi-eye"></i>
          </button>
          <button onclick="openEditCompanyModal(${company.id})" class="btn btn-sm btn-outline-warning" title="Edit">
            <i class="bi bi-pencil"></i>
          </button>
          <button onclick="showDeleteConfirmation(${company.id}, ${JSON.stringify(escapeHtml(company.name || ''))})" class="btn btn-sm btn-outline-danger" title="Delete">
            <i class="bi bi-trash"></i>
          </button>
        </div>
    `;
    
    // Insert at the beginning of container
    container.insertBefore(card, container.firstChild);
    
    // Add fade-in animation
    card.style.opacity = '0';
    card.style.transform = 'translateY(-10px)';
    setTimeout(() => {
        card.style.transition = 'all 0.3s ease';
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, 10);
}

// Function to update existing company row
function updateCompanyRow(company) {
    const card = document.querySelector(`div[data-company-id="${company.id}"]`);
    if (card) {
        // Update card content
        const nameEl = card.querySelector('h3');
        if (nameEl) nameEl.textContent = company.name || '';
        
        const logoContainer = card.querySelector('.flex-1');
        if (logoContainer) {
            let logoImg = logoContainer.querySelector('img');
            if (company.logo_url) {
                if (!logoImg) {
                    logoImg = document.createElement('img');
                    logoImg.className = 'h-12 w-auto mt-2';
                    logoImg.alt = (company.name || '') + ' Logo';
                    logoContainer.appendChild(logoImg);
                }
                logoImg.src = company.logo_url;
            } else if (logoImg) {
                logoImg.remove();
            }
        }
        
        // Update email
        const emailContainer = card.querySelector('.space-y-2');
        if (emailContainer) {
            let emailDiv = emailContainer.querySelector('.bi-envelope')?.closest('div');
            if (company.email) {
                if (!emailDiv) {
                    emailDiv = document.createElement('div');
                    emailDiv.className = 'flex items-center text-sm text-gray-600';
                    emailDiv.innerHTML = '<i class="bi bi-envelope me-2"></i><span class="truncate"></span>';
                    emailContainer.insertBefore(emailDiv, emailContainer.firstChild);
                }
                emailDiv.querySelector('span').textContent = company.email;
            } else if (emailDiv) {
                emailDiv.remove();
            }
        }
        
        // Update phone
        if (emailContainer) {
            let phoneDiv = emailContainer.querySelector('.bi-telephone')?.closest('div');
            if (company.phone) {
                if (!phoneDiv) {
                    phoneDiv = document.createElement('div');
                    phoneDiv.className = 'flex items-center text-sm text-gray-600';
                    phoneDiv.innerHTML = '<i class="bi bi-telephone me-2"></i><span></span>';
                    emailContainer.appendChild(phoneDiv);
                }
                phoneDiv.querySelector('span').textContent = company.phone;
            } else if (phoneDiv) {
                phoneDiv.remove();
            }
        }
        
        // Update address
        if (emailContainer) {
            let addressDiv = emailContainer.querySelector('.bi-geo-alt')?.closest('div');
            if (company.address) {
                const addressText = company.address.length > 50 ? company.address.substring(0, 50) + '...' : company.address;
                if (!addressDiv) {
                    addressDiv = document.createElement('div');
                    addressDiv.className = 'flex items-start text-sm text-gray-600';
                    addressDiv.innerHTML = '<i class="bi bi-geo-alt me-2 mt-0.5"></i><span class="flex-1"></span>';
                    emailContainer.appendChild(addressDiv);
                }
                addressDiv.querySelector('span').textContent = addressText;
            } else if (addressDiv) {
                addressDiv.remove();
            }
        }
        
        // Add highlight animation
        card.style.backgroundColor = '#fef3c7';
        setTimeout(() => {
            card.style.transition = 'background-color 0.5s ease';
            card.style.backgroundColor = '';
        }, 10);
        setTimeout(() => {
            card.style.backgroundColor = '';
        }, 2000);
    }
}

// Function to escape HTML to prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// View Company Functions
function openViewCompanyModal(companyId) {
    // Ensure companyId is a number and not undefined
    if (!companyId || isNaN(companyId)) {
        console.error('Invalid company ID:', companyId);
        showNotification('Invalid company ID', 'error');
        return;
    }
    
    // Clear any previous content
    const modal = document.getElementById('viewCompanyModal');
    const content = document.getElementById('view-company-content');
    
    modal.classList.remove('hidden');
    content.innerHTML = '<div id="view-loading" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div><p class="mt-2 text-gray-600">Loading company details...</p></div>';
    
    // Use the companyId parameter directly, ensure it's a number
    const companyIdNum = parseInt(companyId, 10);
    console.log('Loading company details for ID:', companyIdNum);
    
    fetch(`/admin/companies/${companyIdNum}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        cache: 'no-cache' // Prevent caching
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Failed to load company details');
        }
        return response.json();
    })
    .then(data => {
        if (data.company) {
            const company = data.company;
            console.log('Loaded company:', company.id, company.name);
            
            // Verify the loaded company matches the requested ID
            if (company.id != companyIdNum) {
                console.warn('Company ID mismatch. Requested:', companyIdNum, 'Received:', company.id);
            }
            
            document.getElementById('view-modal-title').textContent = company.name || 'Company Details';
            
            let html = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="md:col-span-1 flex items-start justify-center">
            `;
            
            if (company.logo_url) {
                html += `<img src="${escapeHtml(company.logo_url)}" alt="Logo" class="h-32 rounded shadow bg-white p-2">`;
            } else {
                html += `<div class="h-32 w-32 flex items-center justify-center rounded bg-gray-100 text-gray-600 font-semibold text-center">${escapeHtml(company.name || 'N/A')}</div>`;
            }
            
            html += `
                    </div>
                    <div class="md:col-span-2">
                        <!-- Project Count Card -->
                        <div class="mb-6 bg-gradient-to-br from-indigo-50 to-blue-50 rounded-lg p-6 border border-indigo-100 shadow-sm cursor-pointer hover:shadow-md transition-all duration-200 hover:from-indigo-100 hover:to-blue-100" onclick="viewCompanyProjects(${company.id})" title="Click to view projects">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="bg-indigo-600 rounded-full p-3 shadow-lg">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-600 font-medium mb-1">Total Projects</div>
                                        <div class="text-3xl font-bold text-indigo-700 flex items-center gap-2">
                                            ${company.project_count || 0}
                                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </div>
                                        <div class="text-xs text-indigo-600 mt-1">Click to view all projects</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-gray-500 mb-1">Active Company</div>
                                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                        Active
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Company Details Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Name</div>
                            <div class="font-medium">${escapeHtml(company.name || '—')}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Address</div>
                            <div class="font-medium">${escapeHtml(company.address || '—')}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Email</div>
                            <div class="font-medium">${escapeHtml(company.email || '—')}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Phone</div>
                            <div class="font-medium">${escapeHtml(company.phone || '—')}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Website</div>
                            <div class="font-medium">${company.website ? `<a href="${escapeHtml(company.website)}" target="_blank" class="text-indigo-600 hover:underline">${escapeHtml(company.website)}</a>` : '—'}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Tax Number</div>
                            <div class="font-medium">${escapeHtml(company.tax_number || '—')}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">City</div>
                            <div class="font-medium">${escapeHtml(company.city || '—')}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">State</div>
                            <div class="font-medium">${escapeHtml(company.state || '—')}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">Country</div>
                            <div class="font-medium">${escapeHtml(company.country || '—')}</div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-500 mb-1">ZIP</div>
                            <div class="font-medium">${escapeHtml(company.zip || '—')}</div>
                        </div>
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex space-x-3 justify-end">
                    <button onclick="closeViewCompanyModal(); openEditCompanyModal(${company.id})" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                        Edit
                    </button>
                    <button onclick="closeViewCompanyModal()" class="px-4 py-2 rounded border hover:bg-gray-50 transition">
                        Close
                    </button>
                </div>
            `;
            
            document.getElementById('view-company-content').innerHTML = html;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('view-company-content').innerHTML = `
            <div class="text-center py-8">
                <p class="text-red-600">Failed to load company details.</p>
                <button onclick="closeViewCompanyModal()" class="mt-4 px-4 py-2 rounded border hover:bg-gray-50 transition">
                    Close
                </button>
  </div>
        `;
    });
}

function closeViewCompanyModal() {
    const modal = document.getElementById('viewCompanyModal');
    const content = document.getElementById('view-company-content');
    const title = document.getElementById('view-modal-title');
    
    modal.classList.add('hidden');
    // Clear content when closing
    content.innerHTML = '<div id="view-loading" class="text-center py-8"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div><p class="mt-2 text-gray-600">Loading company details...</p></div>';
    title.textContent = 'Company Details';
}

// Function to view company projects
function viewCompanyProjects(companyId) {
    // Close the company modal first
    closeViewCompanyModal();
    
    // Check if user is super admin (only super admin can switch companies)
    // For regular admins, they can only see their own company's projects
    const userRole = '{{ Auth::user()->role ?? "" }}';
    const projectsUrl = '{{ route("admin.projects.index") }}';
    
    if (userRole === 'super_admin') {
        // For super admin, switch company context first using form data, then navigate via AJAX
        const formData = new FormData();
        formData.append('company_id', companyId);
        formData.append('redirect_to', 'projects');
        formData.append('_token', '{{ csrf_token() }}');
        
        fetch('{{ route("admin.companies.switch") }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Navigate to projects page via AJAX (no page refresh)
                if (typeof window.loadPageViaAjax === 'function') {
                    window.loadPageViaAjax(projectsUrl);
                } else if (typeof loadPageViaAjax === 'function') {
                    loadPageViaAjax(projectsUrl);
                } else {
                    // Fallback: use window.location if loadPageViaAjax is not available
                    window.location.href = projectsUrl;
                }
            } else {
                console.error('Error switching company:', data.error);
                showNotification(data.error || 'Failed to switch company', 'error');
            }
        })
        .catch(error => {
            console.error('Error switching company:', error);
            // Fallback: try AJAX navigation (might work if session is already set)
            if (typeof window.loadPageViaAjax === 'function') {
                window.loadPageViaAjax(projectsUrl);
            } else if (typeof loadPageViaAjax === 'function') {
                loadPageViaAjax(projectsUrl);
            } else {
                window.location.href = projectsUrl;
            }
        });
    } else {
        // For regular admins, navigate to projects via AJAX (they'll see their company's projects)
        if (typeof window.loadPageViaAjax === 'function') {
            window.loadPageViaAjax(projectsUrl);
        } else if (typeof loadPageViaAjax === 'function') {
            loadPageViaAjax(projectsUrl);
        } else {
            window.location.href = projectsUrl;
        }
    }
}

// Close view modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('viewCompanyModal').classList.contains('hidden')) {
        closeViewCompanyModal();
    }
});

// Close view modal when clicking outside
document.getElementById('viewCompanyModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeViewCompanyModal();
    }
});

// Delete Confirmation Functions
function showDeleteConfirmation(companyId, companyName) {
    deleteCompanyId = companyId;
    document.getElementById('delete-company-name').textContent = companyName;
    document.getElementById('delete-company-form').action = `/admin/companies/${companyId}`;
    document.getElementById('deleteConfirmationModal').classList.remove('hidden');
}

function closeDeleteConfirmation() {
    document.getElementById('deleteConfirmationModal').classList.add('hidden');
    deleteCompanyId = null;
}

// Handle delete form submission
document.getElementById('delete-company-form').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Deleting...';
    
    const companyIdToDelete = deleteCompanyId;
    
    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: new FormData(form)
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            // If not JSON, check if it's a successful response
            if (response.ok) {
                return { success: true };
            } else {
                return response.text().then(html => {
                    throw new Error('Server returned HTML instead of JSON');
                });
            }
        }
    })
    .then(data => {
        if (data.success) {
            closeDeleteConfirmation();
            
            // Remove card from container using the stored company ID
            const card = document.querySelector(`div[data-company-id="${companyIdToDelete}"]`);
            
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    card.remove();
                    
                    // Check if container is empty
                    const container = document.getElementById('companiesContainer');
                    if (container && container.children.length === 0) {
                        container.innerHTML = `
                            <div class="col-span-full bg-white rounded-lg shadow p-8 text-center">
                                <p class="text-gray-500 text-lg">No companies found.</p>
                                <button onclick="openCreateCompanyModal()" class="mt-4 bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                                    Create First Company
                                </button>
  </div>
                        `;
                    }
                }, 300);
            } else {
                console.warn('Card not found for company ID:', companyIdToDelete);
                // If card not found, reload the page
                window.location.reload();
            }
            
            showNotification('Company deleted successfully!', 'success');
        } else {
            showNotification(data.message || 'Failed to delete company', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while deleting the company: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

// Close delete modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && !document.getElementById('deleteConfirmationModal').classList.contains('hidden')) {
        closeDeleteConfirmation();
    }
});

// Close delete modal when clicking outside
document.getElementById('deleteConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteConfirmation();
    }
});
</script>
@endpush
@endsection
