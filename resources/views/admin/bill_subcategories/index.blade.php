@extends('admin.layout')

@section('title', 'Bill Subcategories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Bill Subcategories</h1>
    <button onclick="openCreateBillSubcategoryModal()" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> <span class="bill-subcategory-btn-text">Add Subcategory</span>
    </button>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3" id="billSubcategoriesFilterForm">
            <div class="col-md-4">
                <label class="form-label">Filter by Category</label>
                <select name="category_id" id="filter_category_id" class="form-select" onchange="applyFiltersDebounced()">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="button" onclick="resetFilters()" class="btn btn-outline-secondary w-100 mt-4">
                    <i class="bi bi-arrow-clockwise"></i> Reset
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div id="bill-subcategories-loading" class="hidden p-8 text-center">
            <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-2 text-gray-600">Loading bill subcategories...</p>
        </div>
        <div id="bill-subcategories-table-container">
            <div class="overflow-x-auto">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Category</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Sort Order</th>
                            <th>Status</th>
                            <th class="text-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="billSubcategoriesTableBody">
                        @forelse($subcategories as $subcategory)
                            <tr data-bill-subcategory-id="{{ $subcategory->id }}">
                                <td>{{ $subcategory->id }}</td>
                                <td>{{ $subcategory->category->name ?? '—' }}</td>
                                <td>{{ $subcategory->name }}</td>
                                <td>{{ $subcategory->description ?? '—' }}</td>
                                <td>{{ $subcategory->sort_order }}</td>
                                <td>
                                    <span class="badge {{ $subcategory->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $subcategory->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1 text-nowrap">
                                        <button onclick="openViewBillSubcategoryModal({{ $subcategory->id }})" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye me-1"></i> View
                                        </button>
                                        <button onclick="openEditBillSubcategoryModal({{ $subcategory->id }})" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>
                                        <button onclick="showDeleteBillSubcategoryConfirmation({{ $subcategory->id }}, '{{ addslashes($subcategory->name) }}')" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash me-1"></i> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">No subcategories found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3" id="billSubcategoriesPagination">
                <x-pagination :paginator="$subcategories" />
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteBillSubcategoryConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Bill Subcategory</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-bill-subcategory-name"></span>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeleteBillSubcategoryConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeleteBillSubcategory()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="billSubcategoryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="bill-subcategory-modal-title">Add Bill Subcategory</h3>
            <button onclick="closeBillSubcategoryModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="billSubcategoryForm" onsubmit="submitBillSubcategoryForm(event)">
                @csrf
                <input type="hidden" name="_method" id="bill-subcategory-method" value="POST">
                
                <div class="mb-4">
                    <label for="bill-subcategory-category-id" class="block text-sm font-medium text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
                    <select name="bill_category_id" id="bill-subcategory-category-id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select Category</option>
                    </select>
                    <div class="field-error text-red-600 text-sm mt-1" data-field="bill_category_id" style="display: none;"></div>
                </div>

                <div class="mb-4">
                    <label for="bill-subcategory-name" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="bill-subcategory-name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                </div>

                <div class="mb-4">
                    <label for="bill-subcategory-description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="bill-subcategory-description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="bill-subcategory-sort-order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                        <input type="number" name="sort_order" id="bill-subcategory-sort-order" min="0" value="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="bill-subcategory-is-active" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="is_active" id="bill-subcategory-is-active"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeBillSubcategoryModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200" id="bill-subcategory-submit-btn">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="viewBillSubcategoryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Bill Subcategory Details</h3>
            <button onclick="closeViewBillSubcategoryModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6" id="view-bill-subcategory-content">
            <div class="flex items-center justify-center py-12">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .bill-subcategory-btn-text {
            display: none;
        }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentBillSubcategoryId = null;
let deleteBillSubcategoryId = null;
let filterTimeout;

// Debounced filter function for performance
const applyFiltersDebounced = window.debounce ? window.debounce(applyFilters, 300) : applyFilters;

function applyFilters() {
    const form = document.getElementById('billSubcategoriesFilterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    for (const [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    
    const url = '{{ route("admin.bill-subcategories.index") }}?' + params.toString();
    
    // Show loading indicator
    const loadingIndicator = document.getElementById('bill-subcategories-loading');
    const tableContainer = document.getElementById('bill-subcategories-table-container');
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
        updateBillSubcategoriesTable(data.subcategories);
        updateBillSubcategoriesPagination(data.pagination);
        
        // Hide loading indicator
        if (loadingIndicator) loadingIndicator.classList.add('hidden');
        if (tableContainer) tableContainer.style.opacity = '1';
        
        // Update URL without reload
        window.history.pushState({}, '', url);
        
        // Scroll to top of table
        const tableResponsive = document.querySelector('.overflow-x-auto');
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

function updateBillSubcategoriesTable(subcategories) {
    const tbody = document.getElementById('billSubcategoriesTableBody');
    if (!tbody) return;
    
    if (!subcategories || subcategories.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">No subcategories found.</td></tr>';
        return;
    }
    
    tbody.innerHTML = subcategories.map(subcategory => `
        <tr data-bill-subcategory-id="${subcategory.id}">
            <td>${subcategory.id}</td>
            <td>${subcategory.category_name}</td>
            <td>${subcategory.name}</td>
            <td>${subcategory.description}</td>
            <td>${subcategory.sort_order}</td>
            <td>
                <span class="badge ${subcategory.is_active ? 'bg-success' : 'bg-secondary'}">
                    ${subcategory.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>
                <div class="d-flex gap-1 text-nowrap">
                    <button onclick="openViewBillSubcategoryModal(${subcategory.id})" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i> View
                    </button>
                    <button onclick="openEditBillSubcategoryModal(${subcategory.id})" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </button>
                    <button onclick="showDeleteBillSubcategoryConfirmation(${subcategory.id}, '${(subcategory.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function updateBillSubcategoriesPagination(paginationHtml) {
    const paginationContainer = document.getElementById('billSubcategoriesPagination');
    if (!paginationContainer) return;
    
    paginationContainer.innerHTML = paginationHtml || '';
    
    // Attach pagination listeners
    attachPaginationListeners();
}

function resetFilters() {
    document.getElementById('billSubcategoriesFilterForm').reset();
    applyFilters();
}

// Pagination handler
function attachPaginationListeners() {
    const paginationContainer = document.getElementById('billSubcategoriesPagination');
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
    const loadingIndicator = document.getElementById('bill-subcategories-loading');
    const tableContainer = document.getElementById('bill-subcategories-table-container');
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
        updateBillSubcategoriesTable(data.subcategories);
        updateBillSubcategoriesPagination(data.pagination);
        
        // Hide loading indicator
        if (loadingIndicator) loadingIndicator.classList.add('hidden');
        if (tableContainer) tableContainer.style.opacity = '1';
        
        // Update URL without reload
        window.history.pushState({}, '', url);
        
        // Scroll to top of table
        const tableResponsive = document.querySelector('.overflow-x-auto');
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
    
    // Auto-load filters on page load
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.toString()) {
        applyFilters();
    }
});

function openCreateBillSubcategoryModal() {
    currentBillSubcategoryId = null;
    const modal = document.getElementById('billSubcategoryModal');
    const title = document.getElementById('bill-subcategory-modal-title');
    const form = document.getElementById('billSubcategoryForm');
    const methodInput = document.getElementById('bill-subcategory-method');
    const submitBtn = document.getElementById('bill-subcategory-submit-btn');
    const categorySelect = document.getElementById('bill-subcategory-category-id');
    
    modal.classList.remove('hidden');
    title.textContent = 'Add Bill Subcategory';
    methodInput.value = 'POST';
    submitBtn.textContent = 'Save';
    form.reset();
    document.getElementById('bill-subcategory-is-active').value = '1';
    document.getElementById('bill-subcategory-sort-order').value = '0';
    
    // Load categories
    fetch('/admin/bill-subcategories/create', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        categorySelect.innerHTML = '<option value="">Select Category</option>';
        data.categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            if (data.selectedCategoryId && cat.id == data.selectedCategoryId) {
                option.selected = true;
            }
            categorySelect.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error loading categories:', error);
    });
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
}

function openEditBillSubcategoryModal(subcategoryId) {
    currentBillSubcategoryId = subcategoryId;
    const modal = document.getElementById('billSubcategoryModal');
    const title = document.getElementById('bill-subcategory-modal-title');
    const form = document.getElementById('billSubcategoryForm');
    const methodInput = document.getElementById('bill-subcategory-method');
    const submitBtn = document.getElementById('bill-subcategory-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Edit Bill Subcategory';
    methodInput.value = 'PUT';
    submitBtn.textContent = 'Update';
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    // Load subcategory data
    fetch(`/admin/bill-subcategories/${subcategoryId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const categorySelect = document.getElementById('bill-subcategory-category-id');
        categorySelect.innerHTML = '<option value="">Select Category</option>';
        data.categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = cat.name;
            option.selected = cat.id == data.subcategory.bill_category_id;
            categorySelect.appendChild(option);
        });
        
        document.getElementById('bill-subcategory-name').value = data.subcategory.name || '';
        document.getElementById('bill-subcategory-description').value = data.subcategory.description || '';
        document.getElementById('bill-subcategory-sort-order').value = data.subcategory.sort_order || 0;
        document.getElementById('bill-subcategory-is-active').value = data.subcategory.is_active ? '1' : '0';
    })
    .catch(error => {
        console.error('Error loading bill subcategory:', error);
        showNotification('Failed to load bill subcategory data', 'error');
    });
}

function submitBillSubcategoryForm(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('bill-subcategory-submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    const url = currentBillSubcategoryId 
        ? `/admin/bill-subcategories/${currentBillSubcategoryId}`
        : '/admin/bill-subcategories';
    
    if (currentBillSubcategoryId) {
        formData.append('_method', 'PUT');
    }
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            closeBillSubcategoryModal();
            
            // Refresh table after save
            applyFilters();
        } else {
            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const errorEl = document.querySelector(`.field-error[data-field="${field}"]`);
                    if (errorEl) {
                        errorEl.textContent = data.errors[field][0];
                        errorEl.style.display = 'block';
                    }
                });
            }
            showNotification(data.message || 'Validation failed', 'error');
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while saving', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

function closeBillSubcategoryModal() {
    document.getElementById('billSubcategoryModal').classList.add('hidden');
    currentBillSubcategoryId = null;
    document.getElementById('billSubcategoryForm').reset();
}

function addBillSubcategoryRow(subcategory) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = tbody.querySelector('tr td[colspan]');
    
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    const row = document.createElement('tr');
    row.setAttribute('data-bill-subcategory-id', subcategory.id);
    row.innerHTML = `
        <td>${subcategory.id}</td>
        <td>${subcategory.category_name || '—'}</td>
        <td>${subcategory.name}</td>
        <td>${subcategory.description || '—'}</td>
        <td>${subcategory.sort_order || 0}</td>
        <td>
            <span class="badge ${subcategory.is_active ? 'bg-success' : 'bg-secondary'}">
                ${subcategory.is_active ? 'Active' : 'Inactive'}
            </span>
        </td>
        <td>
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewBillSubcategoryModal(${subcategory.id})" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye me-1"></i> View
                </button>
                <button onclick="openEditBillSubcategoryModal(${subcategory.id})" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </button>
                <button onclick="showDeleteBillSubcategoryConfirmation(${subcategory.id}, '${(subcategory.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
}

function updateBillSubcategoryRow(subcategory) {
    const row = document.querySelector(`tr[data-bill-subcategory-id="${subcategory.id}"]`);
    if (row) {
        row.innerHTML = `
            <td>${subcategory.id}</td>
            <td>${subcategory.category_name || '—'}</td>
            <td>${subcategory.name}</td>
            <td>${subcategory.description || '—'}</td>
            <td>${subcategory.sort_order || 0}</td>
            <td>
                <span class="badge ${subcategory.is_active ? 'bg-success' : 'bg-secondary'}">
                    ${subcategory.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>
                <div class="d-flex gap-1 text-nowrap">
                    <button onclick="openViewBillSubcategoryModal(${subcategory.id})" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i> View
                    </button>
                    <button onclick="openEditBillSubcategoryModal(${subcategory.id})" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </button>
                    <button onclick="showDeleteBillSubcategoryConfirmation(${subcategory.id}, '${(subcategory.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </td>
        `;
    }
}

function openViewBillSubcategoryModal(subcategoryId) {
    const modal = document.getElementById('viewBillSubcategoryModal');
    const content = document.getElementById('view-bill-subcategory-content');
    
    modal.classList.remove('hidden');
    content.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    
    fetch(`/admin/bill-subcategories/${subcategoryId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const sub = data.subcategory;
        content.innerHTML = `
            <div class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">${sub.id || ''}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Category</dt>
                    <dd class="mt-1 text-sm text-gray-900">${sub.category_name || ''}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">${sub.name || ''}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">${sub.description || 'N/A'}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Sort Order</dt>
                    <dd class="mt-1 text-sm text-gray-900">${sub.sort_order || 0}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${sub.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                            ${sub.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created At</dt>
                    <dd class="mt-1 text-sm text-gray-900">${sub.created_at || ''}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                    <dd class="mt-1 text-sm text-gray-900">${sub.updated_at || ''}</dd>
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button onclick="closeViewBillSubcategoryModal(); openEditBillSubcategoryModal(${sub.id})" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    Edit
                </button>
                <button onclick="closeViewBillSubcategoryModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    })
    .catch(error => {
        console.error('Error loading bill subcategory:', error);
        content.innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-600 mb-4">Failed to load bill subcategory details</p>
                <button onclick="closeViewBillSubcategoryModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    });
}

function closeViewBillSubcategoryModal() {
    document.getElementById('viewBillSubcategoryModal').classList.add('hidden');
}

function showDeleteBillSubcategoryConfirmation(subcategoryId, subcategoryName) {
    deleteBillSubcategoryId = subcategoryId;
    document.getElementById('delete-bill-subcategory-name').textContent = subcategoryName;
    document.getElementById('deleteBillSubcategoryConfirmationModal').classList.remove('hidden');
}

function closeDeleteBillSubcategoryConfirmation() {
    document.getElementById('deleteBillSubcategoryConfirmationModal').classList.add('hidden');
    deleteBillSubcategoryId = null;
}

function confirmDeleteBillSubcategory() {
    if (!deleteBillSubcategoryId) return;
    
    const subcategoryIdToDelete = deleteBillSubcategoryId;
    const row = document.querySelector(`tr[data-bill-subcategory-id="${subcategoryIdToDelete}"]`);
    const deleteBtn = event.target;
    
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/bill-subcategories/${subcategoryIdToDelete}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeDeleteBillSubcategoryConfirmation();
            showNotification(data.message, 'success');
            
            // Refresh table after delete
            applyFilters();
        } else {
            showNotification(data.message || 'Failed to delete bill subcategory', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error deleting bill subcategory:', error);
        showNotification('An error occurred while deleting', 'error');
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Delete';
    });
}

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
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    `;
    
    document.body.appendChild(notificationDiv);
    
    setTimeout(() => {
        notificationDiv.style.opacity = '0';
        setTimeout(() => notificationDiv.remove(), 300);
    }, 3000);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('billSubcategoryModal').classList.contains('hidden')) {
            closeBillSubcategoryModal();
        }
        if (!document.getElementById('viewBillSubcategoryModal').classList.contains('hidden')) {
            closeViewBillSubcategoryModal();
        }
        if (!document.getElementById('deleteBillSubcategoryConfirmationModal').classList.contains('hidden')) {
            closeDeleteBillSubcategoryConfirmation();
        }
    }
});

document.getElementById('billSubcategoryModal').addEventListener('click', function(e) {
    if (e.target === this) closeBillSubcategoryModal();
});

document.getElementById('viewBillSubcategoryModal').addEventListener('click', function(e) {
    if (e.target === this) closeViewBillSubcategoryModal();
});

document.getElementById('deleteBillSubcategoryConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteBillSubcategoryConfirmation();
});
</script>
@endpush
@endsection
