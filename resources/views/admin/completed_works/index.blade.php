@extends('admin.layout')

@section('title', 'Completed Works')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Completed Works</h1>
        <p class="text-muted mb-0">Track completed work quantities for billing</p>
    </div>
    <div class="d-flex gap-2">
        @if($completedWorks->where('status', '!=', 'billed')->count() > 0)
            <button onclick="openGenerateBillModal()" class="btn btn-success">
                <i class="bi bi-receipt me-1"></i> Generate Bill
            </button>
        @endif
        <button onclick="openCreateCompletedWorkModal()" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Completed Work
        </button>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filters</h5>
    </div>
    <div class="card-body">
        <form id="completedWorksFilterForm" method="GET" action="{{ route('admin.completed-works.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Project</label>
                <select name="project_id" id="filter_project_id" class="form-select form-select-sm" onchange="applyFiltersDebounced()">
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Work Type</label>
                <input type="text" name="work_type" id="filter_work_type" class="form-control form-control-sm" value="{{ request('work_type') }}" placeholder="e.g., Soling, PCC" onkeyup="debounceFilter()">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select name="status" id="filter_status" class="form-select form-select-sm" onchange="applyFiltersDebounced()">
                    <option value="">All Status</option>
                    <option value="recorded" {{ request('status') == 'recorded' ? 'selected' : '' }}>Recorded</option>
                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="billed" {{ request('status') == 'billed' ? 'selected' : '' }}>Billed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Start Date</label>
                <input type="date" name="start_date" id="filter_start_date" class="form-control form-control-sm" value="{{ request('start_date') }}" onchange="applyFiltersDebounced()">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">End Date</label>
                <input type="date" name="end_date" id="filter_end_date" class="form-control form-control-sm" value="{{ request('end_date') }}" onchange="applyFiltersDebounced()">
            </div>
            <div class="col-md-1">
                <button type="button" onclick="resetFilters()" class="btn btn-outline-secondary btn-sm w-100">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </button>
            </div>
        </form>
    </div>
</div>

<div id="completedWorksSummary">
@if($completedWorks->count() > 0)
<div class="card mb-4 shadow-sm">
    <div class="card-body py-3">
        <div class="row g-3 mb-0">
            <div class="col-md-4">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <div class="flex-shrink-0">
                        <i class="bi bi-clipboard-check text-primary fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <small class="text-muted d-block mb-1">Total Records</small>
                        <h5 class="mb-0 text-primary fw-bold">{{ $completedWorks->total() }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <div class="flex-shrink-0">
                        <i class="bi bi-check-circle-fill text-success fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <small class="text-muted d-block mb-1">Total Quantity</small>
                        <h5 class="mb-0 text-success fw-bold">{{ number_format($completedWorks->sum('quantity'), 2) }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <div class="flex-shrink-0">
                        <i class="bi bi-receipt text-info fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <small class="text-muted d-block mb-1">Billed</small>
                        <h5 class="mb-0 text-info fw-bold">{{ $completedWorks->where('status', 'billed')->count() }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
</div>

<div class="card">
    <div class="card-body">
        <div id="completed-works-loading" class="hidden p-8 text-center">
            <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-2 text-gray-600">Loading completed works...</p>
        </div>
        <div id="completed-works-table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Work Date</th>
                            <th>Project</th>
                            <th>Work Type</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Status</th>
                            <th>Bill Item</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="completedWorksTableBody">
                        @forelse($completedWorks as $index => $work)
                            <tr>
                                <td>{{ $completedWorks->firstItem() + $index }}</td>
                                <td>{{ $work->work_date->format('Y-m-d') }}</td>
                                <td>{{ $work->project->name ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-info">{{ $work->work_type }}</span>
                                </td>
                                <td>
                                    <small>{{ Str::limit($work->description, 50) }}</small>
                                </td>
                                <td class="text-end">
                                    <strong>{{ number_format($work->quantity, 2) }}</strong>
                                </td>
                                <td>{{ $work->uom }}</td>
                                <td>
                                    <span class="badge bg-{{ $work->status === 'billed' ? 'success' : ($work->status === 'verified' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($work->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($work->billItem)
                                        <small class="text-muted">{{ $work->billItem->description }}</small>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.completed-works.show', $work) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bi bi-eye me-1"></i> View
                                        </a>
                                        <button onclick="openEditCompletedWorkModal({{ $work->id }})" class="btn btn-outline-warning btn-sm">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>
                                        <form action="{{ route('admin.completed-works.destroy', $work) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <p class="text-muted mb-0">No completed work records found.</p>
                                    <a href="{{ route('admin.completed-works.create') }}" class="btn btn-primary btn-sm mt-2">Add First Record</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div id="completedWorksPagination">
                <x-pagination :paginator="$completedWorks" :show-info="true" />
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let filterTimeout;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

function debounceFilter() {
    clearTimeout(filterTimeout);
    filterTimeout = setTimeout(() => {
        applyFilters();
    }, 500);
}

// Debounced filter function for performance
const applyFiltersDebounced = window.debounce ? window.debounce(applyFilters, 300) : applyFilters;

function applyFilters() {
    const form = document.getElementById('completedWorksFilterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    for (const [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    
    const url = '{{ route("admin.completed-works.index") }}?' + params.toString();
    
    // Show loading indicator
    const loadingIndicator = document.getElementById('completed-works-loading');
    const tableContainer = document.getElementById('completed-works-table-container');
    if (loadingIndicator) loadingIndicator.classList.remove('hidden');
    if (tableContainer) tableContainer.style.opacity = '0.5';
    
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        updateCompletedWorksTable(data.completedWorks);
        updateCompletedWorksPagination(data.pagination);
        updateCompletedWorksSummary(data.summary);
        
        // Hide loading indicator
        if (loadingIndicator) loadingIndicator.classList.add('hidden');
        if (tableContainer) tableContainer.style.opacity = '1';
        
        // Update URL without reload
        window.history.pushState({}, '', url);
        
        // Scroll to top of table
        const tableResponsive = document.querySelector('.table-responsive');
        if (tableResponsive) {
            tableResponsive.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    })
    .catch(error => {
        console.error('Filter error:', error);
        showNotification('Failed to apply filters', 'error');
        // Hide loading indicator on error
        if (loadingIndicator) loadingIndicator.classList.add('hidden');
        if (tableContainer) tableContainer.style.opacity = '1';
    });
}

function updateCompletedWorksTable(completedWorks) {
    const tbody = document.getElementById('completedWorksTableBody');
    if (!tbody) return;
    
    if (!completedWorks || completedWorks.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center py-4">
                    <p class="text-muted mb-0">No completed work records found.</p>
                    <a href="{{ route('admin.completed-works.create') }}" class="btn btn-primary btn-sm mt-2">Add First Record</a>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = completedWorks.map(work => `
        <tr>
            <td>${work.index}</td>
            <td>${work.work_date}</td>
            <td>${work.project_name}</td>
            <td><span class="badge bg-info">${work.work_type}</span></td>
            <td><small>${work.description}</small></td>
            <td class="text-end"><strong>${work.quantity}</strong></td>
            <td>${work.uom}</td>
            <td><span class="badge bg-${work.status_badge_class}">${work.status.charAt(0).toUpperCase() + work.status.slice(1)}</span></td>
            <td><small class="text-muted">${work.bill_item_description}</small></td>
            <td>
                <div class="d-flex gap-1">
                    <a href="${work.show_url}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye me-1"></i> View
                    </a>
                    <a href="${work.edit_url}" class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </a>
                    <form action="${work.destroy_url}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                        <input type="hidden" name="_token" value="${csrfToken}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-trash me-1"></i> Delete
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    `).join('');
}

function updateCompletedWorksPagination(paginationHtml) {
    const paginationContainer = document.getElementById('completedWorksPagination');
    if (!paginationContainer) return;
    
    paginationContainer.innerHTML = paginationHtml || '';
    
    // Attach pagination listeners
    attachPaginationListeners();
}

function updateCompletedWorksSummary(summary) {
    const summaryContainer = document.getElementById('completedWorksSummary');
    if (!summaryContainer) return;
    
    if (!summary || summary.totalRecords === 0) {
        summaryContainer.innerHTML = '';
        return;
    }
    
    summaryContainer.innerHTML = `
        <div class="card mb-4 shadow-sm">
            <div class="card-body py-3">
                <div class="row g-3 mb-0">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <div class="flex-shrink-0">
                                <i class="bi bi-clipboard-check text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block mb-1">Total Records</small>
                                <h5 class="mb-0 text-primary fw-bold">${summary.totalRecords}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <div class="flex-shrink-0">
                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block mb-1">Total Quantity</small>
                                <h5 class="mb-0 text-success fw-bold">${summary.totalQuantity}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <div class="flex-shrink-0">
                                <i class="bi bi-receipt text-info fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block mb-1">Billed</small>
                                <h5 class="mb-0 text-info fw-bold">${summary.billedCount}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function resetFilters() {
    document.getElementById('completedWorksFilterForm').reset();
    applyFilters();
}

// Pagination handler
function attachPaginationListeners() {
    const paginationContainer = document.getElementById('completedWorksPagination');
    if (!paginationContainer) return;
    
    // Remove existing listeners by cloning
    const newContainer = paginationContainer.cloneNode(true);
    paginationContainer.parentNode.replaceChild(newContainer, paginationContainer);
    
    // Attach event listener to container using delegation
    newContainer.addEventListener('click', function(e) {
        const link = e.target.closest('a[href]');
        if (link && link.getAttribute('href')) {
            e.preventDefault();
            const url = link.getAttribute('href');
            handlePaginationClick(url);
        }
    });
}

function handlePaginationClick(url) {
    // Show loading indicator
    const loadingIndicator = document.getElementById('completed-works-loading');
    const tableContainer = document.getElementById('completed-works-table-container');
    if (loadingIndicator) loadingIndicator.classList.remove('hidden');
    if (tableContainer) tableContainer.style.opacity = '0.5';
    
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        updateCompletedWorksTable(data.completedWorks);
        updateCompletedWorksPagination(data.pagination);
        updateCompletedWorksSummary(data.summary);
        
        // Hide loading indicator
        if (loadingIndicator) loadingIndicator.classList.add('hidden');
        if (tableContainer) tableContainer.style.opacity = '1';
        
        // Update URL without reload
        window.history.pushState({}, '', url);
        
        // Scroll to top of table
        const tableResponsive = document.querySelector('.table-responsive');
        if (tableResponsive) {
            tableResponsive.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    })
    .catch(error => {
        console.error('Pagination error:', error);
        showNotification('Failed to load page', 'error');
        // Hide loading indicator on error
        if (loadingIndicator) loadingIndicator.classList.add('hidden');
        if (tableContainer) tableContainer.style.opacity = '1';
        // Reload page as fallback
        window.location.href = url;
    });
}

// Attach pagination listeners on page load
document.addEventListener('DOMContentLoaded', function() {
    attachPaginationListeners();
});

// Generate Bill Modal Functions
function openGenerateBillModal() {
    const modal = document.getElementById('generateBillModal');
    const modalContent = document.getElementById('generateBillModalContent');
    
    if (!modal || !modalContent) return;
    
    // Show modal
    modal.classList.remove('hidden');
    
    // Show loading state
    modalContent.innerHTML = `
        <div class="text-center py-8">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading form...</p>
        </div>
    `;
    
    // Build URL with current filter parameters
    const urlParams = new URLSearchParams(window.location.search);
    const generateBillUrl = '{{ route("admin.completed-works.generate-bill") }}?' + urlParams.toString();
    
    // Load form content via AJAX
    fetch(generateBillUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(html => {
        // Parse HTML and extract form content
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        
        // Find the form in the loaded HTML
        const form = doc.querySelector('#generateBillForm');
        if (!form) {
            throw new Error('Form not found in response');
        }
        
        // Extract form and its content
        const formContent = form.outerHTML;
        
        // Update modal content
        modalContent.innerHTML = formContent;
        
        // Re-execute any scripts from the loaded content
        const scripts = doc.querySelectorAll('script');
        scripts.forEach(function(oldScript) {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });
            if (oldScript.src) {
                newScript.src = oldScript.src;
            } else {
                newScript.textContent = oldScript.textContent || oldScript.innerHTML;
            }
            document.body.appendChild(newScript);
        });
        
        // Attach form submission handler
        attachGenerateBillFormHandler();
    })
    .catch(error => {
        console.error('Error loading generate bill form:', error);
        modalContent.innerHTML = `
            <div class="alert alert-danger">
                <strong>Error:</strong> Failed to load form. Please try again.
                <button onclick="openGenerateBillModal()" class="btn btn-sm btn-outline-danger ms-2">Retry</button>
            </div>
        `;
    });
}

function closeGenerateBillModal() {
    const modal = document.getElementById('generateBillModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function attachGenerateBillFormHandler() {
    const form = document.getElementById('generateBillForm');
    if (!form) return;
    
    // Remove existing listeners by cloning
    const newForm = form.cloneNode(true);
    form.parentNode.replaceChild(newForm, form);
    
    // Attach submit handler
    newForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn ? submitBtn.textContent : 'Generate Bill';
        
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Generating...';
        }
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!response.ok) {
                if (contentType && contentType.includes('application/json')) {
                    return response.json().then(err => {
                        throw err;
                    });
                } else {
                    throw new Error('Server error: ' + response.status);
                }
            }
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // If redirect response, get redirect URL
                const redirectUrl = response.headers.get('Location') || response.url;
                return { success: true, redirect: redirectUrl };
            }
        })
        .then(data => {
            if (data.success) {
                showNotification(data.message || 'Bill generated successfully', 'success');
                setTimeout(() => {
                    closeGenerateBillModal();
                    // If redirect URL provided, navigate to it
                    if (data.redirect) {
                        if (typeof window.loadPageViaAjax === 'function') {
                            window.loadPageViaAjax(data.redirect);
                        } else {
                            window.location.href = data.redirect;
                        }
                    } else {
                        // Refresh the page or reload table
                        applyFilters();
                    }
                }, 500);
            } else {
                if (data.errors) {
                    const errorMessages = Object.values(data.errors).flat().join(', ');
                    showNotification(errorMessages || 'Validation failed', 'error');
                } else {
                    const errorMsg = data.message || data.error || 'Failed to generate bill';
                    showNotification(errorMsg, 'error');
                }
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorMsg = error.message || error.error || 'An error occurred while generating bill';
            showNotification(errorMsg, 'error');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        });
    });
}

// Notification function - define only if not already defined globally
if (typeof window.showNotification !== 'function') {
    window.showNotification = function(message, type = 'success') {
        const notificationDiv = document.createElement('div');
        notificationDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed top-0 end-0 m-3`;
        notificationDiv.style.zIndex = '9999';
        notificationDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notificationDiv);
        
        setTimeout(() => {
            notificationDiv.remove();
        }, 3000);
    };
}

// Local alias for convenience
const showNotification = window.showNotification;

// Completed Work Modal Functions
let currentCompletedWorkId = null;

function openCreateCompletedWorkModal() {
    currentCompletedWorkId = null;
    document.getElementById('completedWorkModalTitle').textContent = 'Add Completed Work';
    document.getElementById('completedWorkForm').reset();
    document.getElementById('completedWorkForm').action = '{{ route("admin.completed-works.store") }}';
    document.getElementById('completedWorkFormMethod').value = 'POST';
    document.getElementById('completedWorkModal').classList.remove('hidden');
    
    // Load form data
    fetch('{{ route("admin.completed-works.create") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Populate projects
        const projectSelect = document.getElementById('modal_project_id');
        projectSelect.innerHTML = '<option value="">Select Project</option>';
        (data.projects || []).forEach(project => {
            const option = document.createElement('option');
            option.value = project.id;
            option.textContent = project.name;
            if (data.selectedProjectId && project.id == data.selectedProjectId) {
                option.selected = true;
            }
            projectSelect.appendChild(option);
        });
        
        // Set default work date
        document.getElementById('modal_work_date').value = new Date().toISOString().split('T')[0];
        
        // Initialize form
        toggleInputMethod();
        updateUOM();
    })
    .catch(error => {
        console.error('Error loading form data:', error);
        showNotification('Failed to load form data', 'error');
    });
}

function openEditCompletedWorkModal(workId) {
    currentCompletedWorkId = workId;
    document.getElementById('completedWorkModalTitle').textContent = 'Edit Completed Work';
    document.getElementById('completedWorkForm').action = `/admin/completed-works/${workId}`;
    document.getElementById('completedWorkFormMethod').value = 'PUT';
    document.getElementById('completedWorkModal').classList.remove('hidden');
    
    // Load work data
    fetch(`/admin/completed-works/${workId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const work = data.completedWork;
        
        // Populate projects
        const projectSelect = document.getElementById('modal_project_id');
        projectSelect.innerHTML = '<option value="">Select Project</option>';
        (data.projects || []).forEach(project => {
            const option = document.createElement('option');
            option.value = project.id;
            option.textContent = project.name;
            if (project.id == work.project_id) option.selected = true;
            projectSelect.appendChild(option);
        });
        
        // Populate form fields
        document.getElementById('modal_work_type').value = work.work_type || '';
        document.getElementById('modal_length').value = work.length || '';
        document.getElementById('modal_width').value = work.width || '';
        document.getElementById('modal_height').value = work.height || '';
        document.getElementById('modal_quantity').value = work.quantity || '';
        document.getElementById('modal_uom').value = work.uom || '';
        document.getElementById('modal_work_date').value = work.work_date || '';
        document.getElementById('modal_description').value = work.description || '';
        
        // Set quantity input method
        const inputMethod = work.quantity_input_method || 'dimensions';
        document.getElementById('modal_input_method_dimensions').checked = inputMethod === 'dimensions';
        document.getElementById('modal_input_method_direct').checked = inputMethod === 'direct';
        
        // Initialize form
        toggleInputMethod();
        updateUOM();
    })
    .catch(error => {
        console.error('Error loading work data:', error);
        showNotification('Failed to load work data', 'error');
    });
}

function closeCompletedWorkModal() {
    document.getElementById('completedWorkModal').classList.add('hidden');
    currentCompletedWorkId = null;
    document.getElementById('completedWorkForm').reset();
    document.getElementById('completedWorkFormMethod').value = 'POST';
    document.getElementById('completedWorkForm').action = '{{ route("admin.completed-works.store") }}';
}

function toggleInputMethod() {
    const inputMethod = document.querySelector('input[name="quantity_input_method"]:checked')?.value || 'dimensions';
    const dimensionsSection = document.getElementById('modal_dimensionsSection');
    const lengthInput = document.getElementById('modal_length');
    const widthInput = document.getElementById('modal_width');
    const heightInput = document.getElementById('modal_height');
    const quantityInput = document.getElementById('modal_quantity');
    const lengthRequired = document.getElementById('modal_lengthRequired');
    const widthRequired = document.getElementById('modal_widthRequired');
    const heightRequired = document.getElementById('modal_heightRequired');
    const quantityRequired = document.getElementById('modal_quantityRequired');
    
    if (inputMethod === 'dimensions') {
        dimensionsSection.style.display = 'block';
        lengthInput.required = true;
        widthInput.required = true;
        heightInput.required = true;
        quantityInput.readOnly = true;
        lengthRequired.style.display = 'inline';
        widthRequired.style.display = 'inline';
        heightRequired.style.display = 'inline';
        quantityRequired.style.display = 'none';
        calculateQuantity();
    } else {
        dimensionsSection.style.display = 'none';
        lengthInput.required = false;
        widthInput.required = false;
        heightInput.required = false;
        quantityInput.readOnly = false;
        lengthRequired.style.display = 'none';
        widthRequired.style.display = 'none';
        heightRequired.style.display = 'none';
        quantityRequired.style.display = 'inline';
        document.getElementById('modal_quantityHint').textContent = 'Enter total quantity directly';
    }
}

function handleQuantityInput() {
    const inputMethod = document.querySelector('input[name="quantity_input_method"]:checked')?.value;
    if (inputMethod === 'direct') {
        const quantityInput = document.getElementById('modal_quantity');
        if (quantityInput.value && parseFloat(quantityInput.value) > 0) {
            document.getElementById('modal_quantityHint').textContent = 'Total quantity entered';
        }
    }
}

function updateUOM() {
    const workType = document.getElementById('modal_work_type').value;
    const uomInput = document.getElementById('modal_uom');
    const quantityHint = document.getElementById('modal_quantityHint');
    const inputMethod = document.querySelector('input[name="quantity_input_method"]:checked')?.value || 'dimensions';
    
    switch(workType) {
        case 'PCC':
        case 'Concrete':
            uomInput.value = 'm³';
            if (inputMethod === 'dimensions') {
                quantityHint.textContent = 'Volume (m³) = Length × Width × Height';
            }
            break;
        case 'Soling':
            uomInput.value = 'm³';
            if (inputMethod === 'dimensions') {
                quantityHint.textContent = 'Volume (m³) = Length × Width × Height';
            }
            break;
        case 'Masonry':
            uomInput.value = 'm³';
            if (inputMethod === 'dimensions') {
                quantityHint.textContent = 'Volume (m³) = Length × Height × Thickness';
            }
            break;
        case 'Plaster':
            uomInput.value = 'm²';
            if (inputMethod === 'dimensions') {
                quantityHint.textContent = 'Area (m²) = Length × Width';
            }
            break;
        default:
            uomInput.value = '';
            quantityHint.textContent = 'Select work type first';
    }
    
    if (inputMethod === 'dimensions') {
        calculateQuantity();
    }
}

function calculateQuantity() {
    const inputMethod = document.querySelector('input[name="quantity_input_method"]:checked')?.value || 'dimensions';
    
    if (inputMethod !== 'dimensions') {
        return;
    }
    
    const workType = document.getElementById('modal_work_type').value;
    const length = parseFloat(document.getElementById('modal_length').value) || 0;
    const width = parseFloat(document.getElementById('modal_width').value) || 0;
    const height = parseFloat(document.getElementById('modal_height').value) || 0;
    const quantityInput = document.getElementById('modal_quantity');
    const descriptionInput = document.getElementById('modal_description');
    
    if (!workType || length <= 0 || width <= 0 || height <= 0) {
        quantityInput.value = '';
        return;
    }
    
    let quantity = 0;
    let description = '';
    
    switch(workType) {
        case 'PCC':
        case 'Concrete':
            quantity = length * width * height;
            description = `${workType} ${length}m × ${width}m × ${height}m`;
            break;
        case 'Soling':
            quantity = length * width * height;
            description = `Soling ${length}m × ${width}m × ${height}m`;
            break;
        case 'Masonry':
            quantity = length * height * width;
            description = `Masonry Wall ${length}m × ${height}m × ${width}m`;
            break;
        case 'Plaster':
            quantity = length * width;
            description = `Plaster ${length}m × ${width}m`;
            break;
    }
    
    quantityInput.value = quantity.toFixed(3);
    
    if (!descriptionInput.value) {
        descriptionInput.value = description;
    }
}

// Form submission
document.addEventListener('DOMContentLoaded', function() {
    const completedWorkForm = document.getElementById('completedWorkForm');
    if (completedWorkForm) {
        completedWorkForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const method = document.getElementById('completedWorkFormMethod').value;
            if (method === 'PUT') {
                formData.append('_method', 'PUT');
            }
            
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';
            
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                const contentType = response.headers.get('content-type');
                if (!response.ok) {
                    if (contentType && contentType.includes('application/json')) {
                        return response.json().then(err => {
                            throw err;
                        });
                    } else {
                        throw new Error('Server error: ' + response.status);
                    }
                }
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                } else {
                    throw new Error('Invalid response format');
                }
            })
            .then(data => {
                if (data.success) {
                    showNotification(data.message || 'Completed work saved successfully', 'success');
                    setTimeout(() => {
                        closeCompletedWorkModal();
                        applyFilters(); // Refresh table
                    }, 500);
                } else {
                    if (data.errors) {
                        const errorMessages = Object.values(data.errors).flat().join(', ');
                        showNotification(errorMessages || 'Validation failed', 'error');
                    } else {
                        const errorMsg = data.message || data.error || 'Failed to save completed work';
                        showNotification(errorMsg, 'error');
                    }
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const errorMsg = error.message || error.error || 'An error occurred while saving';
                showNotification(errorMsg, 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });
    }
    
    // Auto-load filters on page load
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.toString()) {
        applyFilters();
    }
});
</script>
@endpush

<!-- Completed Work Modal -->
<div id="completedWorkModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4" onclick="if(event.target === this) closeCompletedWorkModal()">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[95vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
            <h2 class="text-2xl font-bold text-gray-900" id="completedWorkModalTitle">Add Completed Work</h2>
            <button onclick="closeCompletedWorkModal()" class="text-gray-400 hover:text-gray-600 transition duration-200">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-6">
            <form id="completedWorkForm" method="POST">
                @csrf
                <input type="hidden" id="completedWorkFormMethod" name="_method" value="POST">
                
                <div class="card mb-4">
                    <div class="card-header"><strong>Work Information</strong></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Project *</label>
                                <select name="project_id" id="modal_project_id" class="form-select" required>
                                    <option value="">Select Project</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Work Type *</label>
                                <select name="work_type" id="modal_work_type" class="form-select" required onchange="updateUOM()">
                                    <option value="">Select Work Type</option>
                                    <option value="PCC">PCC (Plain Cement Concrete)</option>
                                    <option value="Soling">Soling / Base</option>
                                    <option value="Masonry">Masonry / Wall</option>
                                    <option value="Plaster">Plaster</option>
                                    <option value="Concrete">Concrete</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">Quantity Input Method *</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="quantity_input_method" id="modal_input_method_dimensions" value="dimensions" checked onchange="toggleInputMethod()">
                                    <label class="btn btn-outline-primary" for="modal_input_method_dimensions">
                                        <i class="bi bi-rulers me-1"></i> Calculate from Dimensions (L × B × H)
                                    </label>
                                    <input type="radio" class="btn-check" name="quantity_input_method" id="modal_input_method_direct" value="direct" onchange="toggleInputMethod()">
                                    <label class="btn btn-outline-primary" for="modal_input_method_direct">
                                        <i class="bi bi-123 me-1"></i> Enter Total Quantity Directly
                                    </label>
                                </div>
                            </div>
                            
                            <div id="modal_dimensionsSection" class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Length (m) <span id="modal_lengthRequired">*</span></label>
                                    <input type="number" name="length" id="modal_length" class="form-control" step="0.001" min="0" oninput="calculateQuantity()">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Width/Breadth (m) <span id="modal_widthRequired">*</span></label>
                                    <input type="number" name="width" id="modal_width" class="form-control" step="0.001" min="0" oninput="calculateQuantity()">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Height/Thickness (m) <span id="modal_heightRequired">*</span></label>
                                    <input type="number" name="height" id="modal_height" class="form-control" step="0.001" min="0" oninput="calculateQuantity()">
                                    <small class="text-muted">Height for walls, Thickness for PCC/Soling/Plaster</small>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Quantity <span id="modal_quantityRequired">*</span></label>
                                <input type="number" name="quantity" id="modal_quantity" class="form-control" step="0.001" min="0" oninput="handleQuantityInput()">
                                <small class="text-muted" id="modal_quantityHint">Auto-calculated based on work type</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Unit of Measurement</label>
                                <input type="text" name="uom" id="modal_uom" class="form-control" readonly>
                                <small class="text-muted">Auto-set based on work type</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Work Date *</label>
                                <input type="date" name="work_date" id="modal_work_date" class="form-control" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="modal_description" class="form-control" rows="2"></textarea>
                                <small class="text-muted">Auto-generated from dimensions if left empty</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="button" onclick="closeCompletedWorkModal()" class="btn btn-secondary me-2">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Completed Work</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Generate Bill Modal -->
<div id="generateBillModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4" onclick="if(event.target === this) closeGenerateBillModal()">
    <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[95vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
            <h2 class="text-2xl font-bold text-gray-900">Generate Bill from Completed Works</h2>
            <button onclick="closeGenerateBillModal()" class="text-gray-400 hover:text-gray-600 transition duration-200">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-6" id="generateBillModalContent">
            <div class="text-center py-8">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 text-muted">Loading form...</p>
            </div>
        </div>
    </div>
</div>
@endsection

