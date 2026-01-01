@extends('admin.layout')

@section('title', 'Construction Final Bills')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Construction Final Bills / Estimates</h1>
        <small class="text-muted">Manage bill modules and BOQ</small>
    </div>
    <button onclick="openCreateBillModal()" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> <span class="d-none d-md-inline">Create Bill</span>
    </button>
</div>

<div class="card mb-4">
    <div class="card-header">
        <strong>Search & Filter</strong>
    </div>
    <div class="card-body">
        <form id="billModulesFilterForm" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Project</label>
                <select name="project_id" class="form-select" onchange="applyFiltersDebounced()">
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ !request('project_id') ? '' : (request('project_id') == $project->id ? 'selected' : '') }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="applyFiltersDebounced()">
                    <option value="">All Status</option>
                    <option value="draft" {{ !request('status') ? '' : (request('status') == 'draft' ? 'selected' : '') }}>Draft</option>
                    <option value="submitted" {{ !request('status') ? '' : (request('status') == 'submitted' ? 'selected' : '') }}>Submitted</option>
                    <option value="approved" {{ !request('status') ? '' : (request('status') == 'approved' ? 'selected' : '') }}>Approved</option>
                    <option value="rejected" {{ !request('status') ? '' : (request('status') == 'rejected' ? 'selected' : '') }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Title or MB Number" onkeyup="debounceFilter()">
            </div>
            <div class="col-md-12 d-flex justify-content-end mt-2">
                <button type="button" onclick="resetFilters()" class="btn btn-outline-secondary me-2">Reset</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <strong>Bill Modules</strong>
    </div>
    <div id="bill-modules-loading" class="hidden p-8 text-center">
        <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-2 text-gray-600">Loading bill modules...</p>
    </div>
    <div id="bill-modules-table-container">
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Project</th>
                        <th>Version</th>
                        <th>MB Number</th>
                        <th>Status</th>
                        <th>Grand Total</th>
                        <th>Created By</th>
                        <th>Created At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="billModulesTableBody">
                    @include('admin.bill_modules.partials.table', ['bills' => $bills])
                </tbody>
            </table>
        </div>
        <div id="billModulesPagination">
            @include('admin.bill_modules.partials.pagination', ['paginator' => $bills])
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
    const form = document.getElementById('billModulesFilterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    for (const [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    
    const url = '{{ route("admin.bill-modules.index") }}?' + params.toString();
    
    // Show loading indicator
    const loadingIndicator = document.getElementById('bill-modules-loading');
    const tableContainer = document.getElementById('bill-modules-table-container');
    if (loadingIndicator) loadingIndicator.classList.remove('hidden');
    if (tableContainer) tableContainer.style.opacity = '0.5';
    
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.tableHtml) {
            // Parse the HTML to extract tbody content
            const parser = new DOMParser();
            const doc = parser.parseFromString(data.tableHtml, 'text/html');
            const tbody = doc.querySelector('tbody');
            
            if (tbody) {
                document.getElementById('billModulesTableBody').innerHTML = tbody.innerHTML;
            } else {
                // Fallback: wrap in table to parse tbody
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = '<table>' + data.tableHtml + '</table>';
                const extractedTbody = tempDiv.querySelector('tbody');
                if (extractedTbody) {
                    document.getElementById('billModulesTableBody').innerHTML = extractedTbody.innerHTML;
                }
            }
        }
        
        if (data.paginationHtml) {
            document.getElementById('billModulesPagination').innerHTML = data.paginationHtml;
            attachPaginationListeners();
        }
        
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

function resetFilters() {
    document.getElementById('billModulesFilterForm').reset();
    applyFilters();
}

// Use event delegation for pagination links
function attachPaginationListeners() {
    const paginationContainer = document.getElementById('billModulesPagination');
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
    const loadingIndicator = document.getElementById('bill-modules-loading');
    const tableContainer = document.getElementById('bill-modules-table-container');
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
        if (data.tableHtml) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(data.tableHtml, 'text/html');
            const tbody = doc.querySelector('tbody');
            
            if (tbody) {
                document.getElementById('billModulesTableBody').innerHTML = tbody.innerHTML;
            } else {
                // Fallback: wrap in table to parse tbody
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = '<table>' + data.tableHtml + '</table>';
                const extractedTbody = tempDiv.querySelector('tbody');
                if (extractedTbody) {
                    document.getElementById('billModulesTableBody').innerHTML = extractedTbody.innerHTML;
                } else {
                    console.error('No tbody found in response');
                }
            }
        }
        
        if (data.paginationHtml) {
            document.getElementById('billModulesPagination').innerHTML = data.paginationHtml;
            attachPaginationListeners();
        }
        
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

// Attach pagination listeners on page load and after filter updates
document.addEventListener('DOMContentLoaded', function() {
    attachPaginationListeners();
});

// Bill Module Modal Functions
let currentBillId = null;
let itemIndex = 0;
let billCategories = [];
let subcategoriesByCategory = {};

function openCreateBillModal() {
    currentBillId = null;
    itemIndex = 0;
    document.getElementById('billModalTitle').textContent = 'Create Bill Module';
    document.getElementById('billForm').reset();
    document.getElementById('billForm').action = '{{ route("admin.bill-modules.store") }}';
    document.getElementById('billFormMethod').value = 'POST';
    document.getElementById('itemsBody').innerHTML = '';
    document.getElementById('billModal').classList.remove('hidden');
    
    // Load form data
    fetch('{{ route("admin.bill-modules.create") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        billCategories = data.billCategories || [];
        subcategoriesByCategory = data.subcategoriesData || {};
        
        // Populate projects
        const projectSelect = document.getElementById('billProjectId');
        projectSelect.innerHTML = '<option value="">Select Project</option>';
        (data.projects || []).forEach(project => {
            const option = document.createElement('option');
            option.value = project.id;
            option.textContent = project.name;
            projectSelect.appendChild(option);
        });
        
        // Add first item row
        addItemRow();
    })
    .catch(error => {
        console.error('Error loading form data:', error);
        showNotification('Failed to load form data', 'error');
    });
}

function openEditBillModal(billId) {
    currentBillId = billId;
    document.getElementById('billModalTitle').textContent = 'Edit Bill Module';
    document.getElementById('billForm').action = `/admin/bill-modules/${billId}`;
    document.getElementById('billFormMethod').value = 'PUT';
    document.getElementById('billModal').classList.remove('hidden');
    
    // Load bill data
    fetch(`/admin/bill-modules/${billId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        billCategories = data.billCategories || [];
        subcategoriesByCategory = data.subcategoriesData || {};
        const bill = data.bill;
        
        // Populate form fields
        document.getElementById('billProjectId').value = bill.project_id || '';
        document.getElementById('billTitle').value = bill.title || '';
        document.getElementById('billVersion').value = bill.version || '1.0';
        document.getElementById('billMbNumber').value = bill.mb_number || '';
        document.getElementById('billMbDate').value = bill.mb_date || '';
        document.getElementById('billOverhead').value = bill.overhead_percent || 10;
        document.getElementById('billContingency').value = bill.contingency_percent || 5;
        document.getElementById('billNotes').value = bill.notes || '';
        
        // Populate projects
        const projectSelect = document.getElementById('billProjectId');
        projectSelect.innerHTML = '<option value="">Select Project</option>';
        (data.projects || []).forEach(project => {
            const option = document.createElement('option');
            option.value = project.id;
            option.textContent = project.name;
            if (project.id == bill.project_id) option.selected = true;
            projectSelect.appendChild(option);
        });
        projectSelect.disabled = true; // Project cannot be changed in edit mode
        
        // Populate items
        document.getElementById('itemsBody').innerHTML = '';
        itemIndex = 0;
        (bill.items || []).forEach(item => {
            addItemRow(item);
        });
        if (bill.items.length === 0) {
            addItemRow();
        }
    })
    .catch(error => {
        console.error('Error loading bill data:', error);
        showNotification('Failed to load bill data', 'error');
    });
}

function closeBillModal() {
    document.getElementById('billModal').classList.add('hidden');
    currentBillId = null;
    itemIndex = 0;
    const form = document.getElementById('billForm');
    form.reset();
    document.getElementById('itemsBody').innerHTML = '';
    document.getElementById('billFormMethod').value = 'POST';
    document.getElementById('billForm').action = '{{ route("admin.bill-modules.store") }}';
    
    // Reset submit button
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Save Bill';
    }
}

function addItemRow(itemData = null) {
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    row.className = 'item-row';
    const index = itemIndex;
    
    const categoryOptions = billCategories.map(cat => 
        `<option value="${cat.id}" ${itemData && itemData.bill_category_id == cat.id ? 'selected' : ''}>${cat.name}</option>`
    ).join('');
    
    row.innerHTML = `
        <td>${tbody.children.length + 1}</td>
        <td>
            ${itemData && itemData.id ? `<input type="hidden" name="items[${index}][id]" value="${itemData.id}">` : ''}
            <select name="items[${index}][bill_category_id]" class="form-select form-select-sm category-select" required onchange="loadSubcategories(this, ${index})">
                <option value="">Select Category</option>
                ${categoryOptions}
            </select>
        </td>
        <td>
            <select name="items[${index}][bill_subcategory_id]" class="form-select form-select-sm subcategory-select" id="subcategory_${index}">
                <option value="">Select Subcategory</option>
            </select>
        </td>
        <td><input type="text" name="items[${index}][description]" class="form-control form-control-sm" value="${itemData ? (itemData.description || '') : ''}" required></td>
        <td><input type="text" name="items[${index}][uom]" class="form-control form-control-sm" value="${itemData ? (itemData.uom || '') : ''}" required></td>
        <td><input type="number" name="items[${index}][quantity]" class="form-control form-control-sm" step="0.001" min="0" value="${itemData ? (itemData.quantity || '') : ''}" required></td>
        <td><input type="number" name="items[${index}][wastage_percent]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="${itemData ? (itemData.wastage_percent || 0) : 0}"></td>
        <td><input type="number" name="items[${index}][unit_rate]" class="form-control form-control-sm" step="0.01" min="0" value="${itemData ? (itemData.unit_rate || '') : ''}" required></td>
        <td><input type="number" name="items[${index}][tax_percent]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="${itemData ? (itemData.tax_percent || 13) : 13}"></td>
        <td><input type="text" name="items[${index}][remarks]" class="form-control form-control-sm" value="${itemData ? (itemData.remarks || '') : ''}"></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeItemRow(this)">×</button></td>
    `;
    tbody.appendChild(row);
    itemIndex++;
    updateRowNumbers();
    
    // Load subcategories if category is selected
    if (itemData && itemData.bill_category_id) {
        const categorySelect = row.querySelector('.category-select');
        setTimeout(() => {
            loadSubcategories(categorySelect, index);
            if (itemData.bill_subcategory_id) {
                const subcategorySelect = document.getElementById(`subcategory_${index}`);
                if (subcategorySelect) subcategorySelect.value = itemData.bill_subcategory_id;
            }
        }, 100);
    }
}

function removeItemRow(btn) {
    if (document.querySelectorAll('.item-row').length <= 1) {
        showNotification('At least one item is required', 'warning');
        return;
    }
    btn.closest('tr').remove();
    updateRowNumbers();
}

function updateRowNumbers() {
    document.querySelectorAll('.item-row').forEach((row, index) => {
        row.querySelector('td:first-child').textContent = index + 1;
    });
}

function loadSubcategories(selectElement, rowIndex) {
    const categoryId = selectElement.value;
    const subcategorySelect = document.getElementById(`subcategory_${rowIndex}`);
    
    subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
    
    if (categoryId && subcategoriesByCategory[categoryId]) {
        const subcategories = subcategoriesByCategory[categoryId];
        subcategories.forEach(sub => {
            const option = document.createElement('option');
            option.value = sub.id;
            option.textContent = sub.name;
            subcategorySelect.appendChild(option);
        });
    }
}

// Notification function
function showNotification(message, type = 'success') {
    const notificationDiv = document.createElement('div');
    notificationDiv.className = `fixed top-4 right-4 px-6 py-4 rounded-lg shadow-2xl z-50 transition-all duration-300 flex items-center gap-3 min-w-[300px] max-w-[500px]`;
    
    if (type === 'success') {
        notificationDiv.className += ' bg-green-500 text-white';
    } else if (type === 'error') {
        notificationDiv.className += ' bg-red-500 text-white';
    } else if (type === 'warning') {
        notificationDiv.className += ' bg-yellow-500 text-white';
    } else {
        notificationDiv.className += ' bg-blue-500 text-white';
    }
    
    notificationDiv.innerHTML = `
        <span>${message}</span>
        <button onclick="this.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
            <i class="bi bi-x-lg"></i>
        </button>
    `;
    
    document.body.appendChild(notificationDiv);
    
    setTimeout(() => {
        notificationDiv.style.opacity = '0';
        setTimeout(() => notificationDiv.remove(), 300);
    }, 3000);
}

// Form submission
document.getElementById('billForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const method = document.getElementById('billFormMethod').value;
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
            showNotification(data.message || 'Bill saved successfully', 'success');
            setTimeout(() => {
                closeBillModal();
                applyFilters(); // Refresh table
            }, 500);
        } else {
            // Handle validation errors
            if (data.errors) {
                const errorMessages = Object.values(data.errors).flat().join(', ');
                showNotification(errorMessages || 'Validation failed', 'error');
            } else {
                const errorMsg = data.message || data.error || 'Failed to save bill';
                showNotification(errorMsg, 'error');
            }
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        const errorMsg = error.message || error.error || 'An error occurred while saving the bill';
        showNotification(errorMsg, 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});
</script>
@endpush

<!-- Bill Module Modal -->
<div id="billModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4" onclick="if(event.target === this) closeBillModal()">
    <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[95vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between z-10">
            <h2 class="text-2xl font-bold text-gray-900" id="billModalTitle">Create Bill Module</h2>
            <button onclick="closeBillModal()" class="text-gray-400 hover:text-gray-600 transition duration-200">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>
        
        <div class="flex-1 overflow-y-auto p-6">
            <form id="billForm" method="POST">
                @csrf
                <input type="hidden" id="billFormMethod" name="_method" value="POST">
                
                <div class="card mb-4">
                    <div class="card-header"><strong>Bill Information</strong></div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Project *</label>
                                <select id="billProjectId" name="project_id" class="form-select" required>
                                    <option value="">Select Project</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Title *</label>
                                <input type="text" id="billTitle" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Version</label>
                                <input type="text" id="billVersion" name="version" class="form-control" value="1.0">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">MB Number</label>
                                <input type="text" id="billMbNumber" name="mb_number" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">MB Date</label>
                                <input type="date" id="billMbDate" name="mb_date" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Overhead %</label>
                                <input type="number" id="billOverhead" name="overhead_percent" class="form-control" value="10" step="0.01" min="0" max="100">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Contingency %</label>
                                <input type="number" id="billContingency" name="contingency_percent" class="form-control" value="5" step="0.01" min="0" max="100">
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Notes</label>
                                <textarea id="billNotes" name="notes" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>Bill Items</strong>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addItemRow()">
                            <i class="bi bi-plus-circle me-1"></i> Add Item
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th width="5%">S.N.</th>
                                        <th width="12%">Category *</th>
                                        <th width="12%">Subcategory</th>
                                        <th width="18%">Description *</th>
                                        <th width="8%">UOM *</th>
                                        <th width="8%">Quantity *</th>
                                        <th width="7%">Wastage %</th>
                                        <th width="8%">Unit Rate *</th>
                                        <th width="8%">Tax %</th>
                                        <th width="8%">Remarks</th>
                                        <th width="6%">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end">
                    <button type="button" onclick="closeBillModal()" class="btn btn-secondary me-2">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Bill</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

