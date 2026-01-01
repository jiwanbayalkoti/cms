@extends('admin.layout')

@section('title', 'Bill Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Bill Categories</h1>
    <button onclick="openCreateBillCategoryModal()" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> <span class="bill-category-btn-text">Add Category</span>
    </button>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th>Subcategories</th>
                        <th class="text-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr data-bill-category-id="{{ $category->id }}">
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->description ?? '—' }}</td>
                            <td>{{ $category->sort_order }}</td>
                            <td>
                                <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>{{ $category->subcategories->count() }}</td>
                            <td>
                                <div class="d-flex gap-1 text-nowrap">
                                    <button onclick="openViewBillCategoryModal({{ $category->id }})" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> View
                                    </button>
                                    <button onclick="openEditBillCategoryModal({{ $category->id }})" class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </button>
                                    <button onclick="showDeleteBillCategoryConfirmation({{ $category->id }}, '{{ addslashes($category->name) }}')" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-3">No categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="p-3">
                <x-pagination :paginator="$categories" />
            </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteBillCategoryConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Bill Category</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-bill-category-name"></span>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeleteBillCategoryConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeleteBillCategory()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="billCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="bill-category-modal-title">Add Bill Category</h3>
            <button onclick="closeBillCategoryModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="billCategoryForm" onsubmit="submitBillCategoryForm(event)">
                @csrf
                <input type="hidden" name="_method" id="bill-category-method" value="POST">
                
                <div class="mb-4">
                    <label for="bill-category-name" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="bill-category-name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                </div>

                <div class="mb-4">
                    <label for="bill-category-description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="bill-category-description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="bill-category-sort-order" class="block text-sm font-medium text-gray-700 mb-2">Sort Order</label>
                        <input type="number" name="sort_order" id="bill-category-sort-order" min="0" value="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="bill-category-is-active" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="is_active" id="bill-category-is-active"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeBillCategoryModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200" id="bill-category-submit-btn">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="viewBillCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Bill Category Details</h3>
            <button onclick="closeViewBillCategoryModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6" id="view-bill-category-content">
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
        .bill-category-btn-text {
            display: none;
        }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentBillCategoryId = null;
let deleteBillCategoryId = null;

function openCreateBillCategoryModal() {
    currentBillCategoryId = null;
    const modal = document.getElementById('billCategoryModal');
    const title = document.getElementById('bill-category-modal-title');
    const form = document.getElementById('billCategoryForm');
    const methodInput = document.getElementById('bill-category-method');
    const submitBtn = document.getElementById('bill-category-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Add Bill Category';
    methodInput.value = 'POST';
    submitBtn.textContent = 'Save';
    form.reset();
    document.getElementById('bill-category-is-active').value = '1';
    document.getElementById('bill-category-sort-order').value = '0';
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
}

function openEditBillCategoryModal(categoryId) {
    currentBillCategoryId = categoryId;
    const modal = document.getElementById('billCategoryModal');
    const title = document.getElementById('bill-category-modal-title');
    const form = document.getElementById('billCategoryForm');
    const methodInput = document.getElementById('bill-category-method');
    const submitBtn = document.getElementById('bill-category-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Edit Bill Category';
    methodInput.value = 'PUT';
    submitBtn.textContent = 'Update';
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    fetch(`/admin/bill-categories/${categoryId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('bill-category-name').value = data.category.name || '';
        document.getElementById('bill-category-description').value = data.category.description || '';
        document.getElementById('bill-category-sort-order').value = data.category.sort_order || 0;
        document.getElementById('bill-category-is-active').value = data.category.is_active ? '1' : '0';
    })
    .catch(error => {
        console.error('Error loading bill category:', error);
        showNotification('Failed to load bill category data', 'error');
    });
}

function submitBillCategoryForm(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('bill-category-submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    const url = currentBillCategoryId 
        ? `/admin/bill-categories/${currentBillCategoryId}`
        : '/admin/bill-categories';
    
    if (currentBillCategoryId) {
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
            closeBillCategoryModal();
            
            if (currentBillCategoryId) {
                updateBillCategoryRow(data.category);
            } else {
                addBillCategoryRow(data.category);
            }
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

function closeBillCategoryModal() {
    document.getElementById('billCategoryModal').classList.add('hidden');
    currentBillCategoryId = null;
    document.getElementById('billCategoryForm').reset();
}

function addBillCategoryRow(category) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = tbody.querySelector('tr td[colspan]');
    
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    const row = document.createElement('tr');
    row.setAttribute('data-bill-category-id', category.id);
    row.innerHTML = `
        <td>${category.id}</td>
        <td>${category.name}</td>
        <td>${category.description || '—'}</td>
        <td>${category.sort_order || 0}</td>
        <td>
            <span class="badge ${category.is_active ? 'bg-success' : 'bg-secondary'}">
                ${category.is_active ? 'Active' : 'Inactive'}
            </span>
        </td>
        <td>${category.subcategories_count || 0}</td>
        <td>
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewBillCategoryModal(${category.id})" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye me-1"></i> View
                </button>
                <button onclick="openEditBillCategoryModal(${category.id})" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </button>
                <button onclick="showDeleteBillCategoryConfirmation(${category.id}, '${(category.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
}

function updateBillCategoryRow(category) {
    const row = document.querySelector(`tr[data-bill-category-id="${category.id}"]`);
    if (row) {
        row.innerHTML = `
            <td>${category.id}</td>
            <td>${category.name}</td>
            <td>${category.description || '—'}</td>
            <td>${category.sort_order || 0}</td>
            <td>
                <span class="badge ${category.is_active ? 'bg-success' : 'bg-secondary'}">
                    ${category.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td>${category.subcategories_count || 0}</td>
            <td>
                <div class="d-flex gap-1 text-nowrap">
                    <button onclick="openViewBillCategoryModal(${category.id})" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i> View
                    </button>
                    <button onclick="openEditBillCategoryModal(${category.id})" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </button>
                    <button onclick="showDeleteBillCategoryConfirmation(${category.id}, '${(category.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </td>
        `;
    }
}

function openViewBillCategoryModal(categoryId) {
    const modal = document.getElementById('viewBillCategoryModal');
    const content = document.getElementById('view-bill-category-content');
    
    modal.classList.remove('hidden');
    content.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    
    fetch(`/admin/bill-categories/${categoryId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const cat = data.category;
        content.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Category Information</h2>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-sm text-gray-900">${cat.name || ''}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900">${cat.description || 'N/A'}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Sort Order</dt>
                            <dd class="mt-1 text-sm text-gray-900">${cat.sort_order || 0}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${cat.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                    ${cat.is_active ? 'Active' : 'Inactive'}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900">${cat.created_at || ''}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                            <dd class="mt-1 text-sm text-gray-900">${cat.updated_at || ''}</dd>
                        </div>
                    </dl>
                </div>
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-900">Subcategories</h2>
                    </div>
                    ${cat.subcategories && cat.subcategories.length > 0 ? `
                        <ul class="space-y-2">
                            ${cat.subcategories.map(sub => `
                                <li class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                    <div>
                                        <span class="font-medium text-gray-900">${sub.name}</span>
                                        ${sub.description ? `<p class="text-sm text-gray-500">${sub.description.substring(0, 30)}</p>` : ''}
                                    </div>
                                    <span class="px-2 text-xs font-semibold rounded-full ${sub.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                                        ${sub.is_active ? 'Active' : 'Inactive'}
                                    </span>
                                </li>
                            `).join('')}
                        </ul>
                    ` : '<p class="text-sm text-gray-500">No subcategories found.</p>'}
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button onclick="closeViewBillCategoryModal(); openEditBillCategoryModal(${cat.id})" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    Edit
                </button>
                <button onclick="closeViewBillCategoryModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    })
    .catch(error => {
        console.error('Error loading bill category:', error);
        content.innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-600 mb-4">Failed to load bill category details</p>
                <button onclick="closeViewBillCategoryModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    });
}

function closeViewBillCategoryModal() {
    document.getElementById('viewBillCategoryModal').classList.add('hidden');
}

function showDeleteBillCategoryConfirmation(categoryId, categoryName) {
    deleteBillCategoryId = categoryId;
    document.getElementById('delete-bill-category-name').textContent = categoryName;
    document.getElementById('deleteBillCategoryConfirmationModal').classList.remove('hidden');
}

function closeDeleteBillCategoryConfirmation() {
    document.getElementById('deleteBillCategoryConfirmationModal').classList.add('hidden');
    deleteBillCategoryId = null;
}

function confirmDeleteBillCategory() {
    if (!deleteBillCategoryId) return;
    
    const categoryIdToDelete = deleteBillCategoryId;
    const row = document.querySelector(`tr[data-bill-category-id="${categoryIdToDelete}"]`);
    const deleteBtn = event.target;
    
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/bill-categories/${categoryIdToDelete}`, {
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
            closeDeleteBillCategoryConfirmation();
            showNotification(data.message, 'success');
            
            if (row) {
                row.style.transition = 'opacity 0.3s, transform 0.3s';
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    row.remove();
                    const tbody = document.querySelector('table tbody');
                    if (tbody && tbody.children.length === 0) {
                        tbody.innerHTML = `
                            <tr><td colspan="7" class="text-center text-muted py-3">No categories found.</td></tr>
                        `;
                    }
                }, 300);
            }
        } else {
            showNotification(data.message || 'Failed to delete bill category', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error deleting bill category:', error);
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
        if (!document.getElementById('billCategoryModal').classList.contains('hidden')) {
            closeBillCategoryModal();
        }
        if (!document.getElementById('viewBillCategoryModal').classList.contains('hidden')) {
            closeViewBillCategoryModal();
        }
        if (!document.getElementById('deleteBillCategoryConfirmationModal').classList.contains('hidden')) {
            closeDeleteBillCategoryConfirmation();
        }
    }
});

document.getElementById('billCategoryModal').addEventListener('click', function(e) {
    if (e.target === this) closeBillCategoryModal();
});

document.getElementById('viewBillCategoryModal').addEventListener('click', function(e) {
    if (e.target === this) closeViewBillCategoryModal();
});

document.getElementById('deleteBillCategoryConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteBillCategoryConfirmation();
});
</script>
@endpush
@endsection
