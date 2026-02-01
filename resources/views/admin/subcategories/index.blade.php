@extends('admin.layout')

@section('title', 'Subcategories')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-gray-900">Subcategories</h1>
    <button onclick="openCreateSubcategoryModal()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition duration-200 flex items-center">
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
        <span class="subcategory-btn-text">Add New Subcategory</span>
    </button>
</div>

<div class="bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($subcategories as $subcategory)
                    <tr data-subcategory-id="{{ $subcategory->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">{{ ($subcategories->currentPage() - 1) * $subcategories->perPage() + $loop->iteration }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $subcategory->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">
                                {{ $subcategory->category->name }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-500">{{ Str::limit($subcategory->description, 50) }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $subcategory->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $subcategory->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="d-flex gap-1 text-nowrap">
                                <button onclick="openViewSubcategoryModal({{ $subcategory->id }})" class="btn btn-outline-primary btn-sm" title="View">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button onclick="openEditSubcategoryModal({{ $subcategory->id }})" class="btn btn-outline-warning btn-sm" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="showDeleteSubcategoryConfirmation({{ $subcategory->id }}, '{{ addslashes($subcategory->name) }}')" class="btn btn-outline-danger btn-sm" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                            No subcategories found. <button onclick="openCreateSubcategoryModal()" class="text-indigo-600 hover:text-indigo-900">Create one now</button>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($subcategories->hasPages())
    <div class="mt-4">
        <x-pagination :paginator="$subcategories" />
    </div>
@endif

<!-- Delete Subcategory Confirmation Modal -->
<div id="deleteSubcategoryConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Subcategory</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-subcategory-name"></span>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeleteSubcategoryConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeleteSubcategory()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Subcategory Modal -->
<div id="subcategoryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="subcategory-modal-title">Add New Subcategory</h3>
            <button onclick="closeSubcategoryModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6" id="subcategory-modal-content">
            <form id="subcategoryForm" onsubmit="submitSubcategoryForm(event)">
                @csrf
                <input type="hidden" name="_method" id="subcategory-method" value="POST">
                
                <div class="mb-4">
                    <label for="subcategory-category-id" class="block text-sm font-medium text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
                    <select name="category_id" id="subcategory-category-id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select a category</option>
                    </select>
                    <div class="field-error text-red-600 text-sm mt-1" data-field="category_id" style="display: none;"></div>
                </div>

                <div class="mb-4">
                    <label for="subcategory-name" class="block text-sm font-medium text-gray-700 mb-2">Subcategory Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="subcategory-name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                </div>

                <div class="mb-4">
                    <label for="subcategory-description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="subcategory-description" rows="4"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" id="subcategory-is-active" value="1" checked
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeSubcategoryModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200" id="subcategory-submit-btn">
                        Create Subcategory
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Subcategory Modal -->
<div id="viewSubcategoryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Subcategory Details</h3>
            <button onclick="closeViewSubcategoryModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6" id="view-subcategory-content">
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
        .subcategory-btn-text {
            display: none;
        }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentSubcategoryId = null;
let deleteSubcategoryId = null;

// Open Create Subcategory Modal
function openCreateSubcategoryModal() {
    currentSubcategoryId = null;
    const modal = document.getElementById('subcategoryModal');
    const title = document.getElementById('subcategory-modal-title');
    const form = document.getElementById('subcategoryForm');
    const methodInput = document.getElementById('subcategory-method');
    const submitBtn = document.getElementById('subcategory-submit-btn');
    const categorySelect = document.getElementById('subcategory-category-id');
    
    modal.classList.remove('hidden');
    title.textContent = 'Add New Subcategory';
    methodInput.value = 'POST';
    submitBtn.textContent = 'Create Subcategory';
    form.reset();
    document.getElementById('subcategory-is-active').checked = true;
    
    // Load categories
    fetch('/admin/subcategories/create', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        categorySelect.innerHTML = '<option value="">Select a category</option>';
        data.categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = `${cat.name} (${cat.type.charAt(0).toUpperCase() + cat.type.slice(1)})`;
            categorySelect.appendChild(option);
        });
    })
    .catch(error => {
        console.error('Error loading categories:', error);
    });
    
    // Clear errors
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
}

// Open Edit Subcategory Modal
function openEditSubcategoryModal(subcategoryId) {
    currentSubcategoryId = subcategoryId;
    const modal = document.getElementById('subcategoryModal');
    const title = document.getElementById('subcategory-modal-title');
    const form = document.getElementById('subcategoryForm');
    const methodInput = document.getElementById('subcategory-method');
    const submitBtn = document.getElementById('subcategory-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Edit Subcategory';
    methodInput.value = 'PUT';
    submitBtn.textContent = 'Update Subcategory';
    
    // Clear errors
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    // Load subcategory data
    fetch(`/admin/subcategories/${subcategoryId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const categorySelect = document.getElementById('subcategory-category-id');
        categorySelect.innerHTML = '<option value="">Select a category</option>';
        data.categories.forEach(cat => {
            const option = document.createElement('option');
            option.value = cat.id;
            option.textContent = `${cat.name} (${cat.type.charAt(0).toUpperCase() + cat.type.slice(1)})`;
            option.selected = cat.id == data.subcategory.category_id;
            categorySelect.appendChild(option);
        });
        
        document.getElementById('subcategory-name').value = data.subcategory.name || '';
        document.getElementById('subcategory-description').value = data.subcategory.description || '';
        document.getElementById('subcategory-is-active').checked = data.subcategory.is_active || false;
    })
    .catch(error => {
        console.error('Error loading subcategory:', error);
        showNotification('Failed to load subcategory data', 'error');
    });
}

// Submit Subcategory Form
function submitSubcategoryForm(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('subcategory-submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    const url = currentSubcategoryId 
        ? `/admin/subcategories/${currentSubcategoryId}`
        : '/admin/subcategories';
    
    if (currentSubcategoryId) {
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
            closeSubcategoryModal();
            
            if (currentSubcategoryId) {
                updateSubcategoryRow(data.subcategory);
            } else {
                addSubcategoryRow(data.subcategory);
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

// Close Subcategory Modal
function closeSubcategoryModal() {
    document.getElementById('subcategoryModal').classList.add('hidden');
    currentSubcategoryId = null;
    document.getElementById('subcategoryForm').reset();
}

// Add Subcategory Row
function addSubcategoryRow(subcategory) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = tbody.querySelector('tr td[colspan]');
    
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    const row = document.createElement('tr');
    row.setAttribute('data-subcategory-id', subcategory.id);
    row.innerHTML = `
        <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">${subcategory.name}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm text-gray-900">${subcategory.category_name || ''}</span>
        </td>
        <td class="px-6 py-4">
            <div class="text-sm text-gray-500">${(subcategory.description || '').substring(0, 50)}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${subcategory.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                ${subcategory.is_active ? 'Active' : 'Inactive'}
            </span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewSubcategoryModal(${subcategory.id})" class="btn btn-outline-primary btn-sm" title="View">
                    <i class="bi bi-eye"></i>
                </button>
                <button onclick="openEditSubcategoryModal(${subcategory.id})" class="btn btn-outline-warning btn-sm" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeleteSubcategoryConfirmation(${subcategory.id}, '${(subcategory.name || '').replace(/'/g, "\\'")}')" class="btn btn-outline-danger btn-sm" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
}

// Update Subcategory Row
function updateSubcategoryRow(subcategory) {
    const row = document.querySelector(`tr[data-subcategory-id="${subcategory.id}"]`);
    if (row) {
        row.innerHTML = `
            <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">${subcategory.name}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-gray-900">${subcategory.category_name || ''}</span>
            </td>
            <td class="px-6 py-4">
                <div class="text-sm text-gray-500">${(subcategory.description || '').substring(0, 50)}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${subcategory.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                    ${subcategory.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="d-flex gap-1 text-nowrap">
                    <button onclick="openViewSubcategoryModal(${subcategory.id})" class="btn btn-outline-primary btn-sm" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button onclick="openEditSubcategoryModal(${subcategory.id})" class="btn btn-outline-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button onclick="showDeleteSubcategoryConfirmation(${subcategory.id}, '${(subcategory.name || '').replace(/'/g, "\\'")}')" class="btn btn-outline-danger btn-sm" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
    }
}

// Open View Subcategory Modal
function openViewSubcategoryModal(subcategoryId) {
    const modal = document.getElementById('viewSubcategoryModal');
    const content = document.getElementById('view-subcategory-content');
    
    modal.classList.remove('hidden');
    content.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    
    fetch(`/admin/subcategories/${subcategoryId}`, {
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
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">${sub.name || ''}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Category</dt>
                    <dd class="mt-1 text-sm text-gray-900">${sub.category_name || ''}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                    <dd class="mt-1 text-sm text-gray-900">${sub.description || 'N/A'}</dd>
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
                <button onclick="closeViewSubcategoryModal(); openEditSubcategoryModal(${sub.id})" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    Edit
                </button>
                <button onclick="closeViewSubcategoryModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    })
    .catch(error => {
        console.error('Error loading subcategory:', error);
        content.innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-600 mb-4">Failed to load subcategory details</p>
                <button onclick="closeViewSubcategoryModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    });
}

// Close View Subcategory Modal
function closeViewSubcategoryModal() {
    document.getElementById('viewSubcategoryModal').classList.add('hidden');
}

// Delete Subcategory Confirmation
function showDeleteSubcategoryConfirmation(subcategoryId, subcategoryName) {
    deleteSubcategoryId = subcategoryId;
    document.getElementById('delete-subcategory-name').textContent = subcategoryName;
    document.getElementById('deleteSubcategoryConfirmationModal').classList.remove('hidden');
}

function closeDeleteSubcategoryConfirmation() {
    document.getElementById('deleteSubcategoryConfirmationModal').classList.add('hidden');
    deleteSubcategoryId = null;
}

function confirmDeleteSubcategory() {
    if (!deleteSubcategoryId) return;
    
    const subcategoryIdToDelete = deleteSubcategoryId;
    const row = document.querySelector(`tr[data-subcategory-id="${subcategoryIdToDelete}"]`);
    const deleteBtn = event.target;
    
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/subcategories/${subcategoryIdToDelete}`, {
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
            closeDeleteSubcategoryConfirmation();
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
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No subcategories found. <button onclick="openCreateSubcategoryModal()" class="text-indigo-600 hover:text-indigo-900">Create one now</button>
                                </td>
                            </tr>
                        `;
                    }
                }, 300);
            }
        } else {
            showNotification(data.message || 'Failed to delete subcategory', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error deleting subcategory:', error);
        showNotification('An error occurred while deleting', 'error');
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Delete';
    });
}

// Show Notification
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

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('subcategoryModal').classList.contains('hidden')) {
            closeSubcategoryModal();
        }
        if (!document.getElementById('viewSubcategoryModal').classList.contains('hidden')) {
            closeViewSubcategoryModal();
        }
        if (!document.getElementById('deleteSubcategoryConfirmationModal').classList.contains('hidden')) {
            closeDeleteSubcategoryConfirmation();
        }
    }
});

// Close modals when clicking outside
document.getElementById('subcategoryModal').addEventListener('click', function(e) {
    if (e.target === this) closeSubcategoryModal();
});

document.getElementById('viewSubcategoryModal').addEventListener('click', function(e) {
    if (e.target === this) closeViewSubcategoryModal();
});

document.getElementById('deleteSubcategoryConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteSubcategoryConfirmation();
});
</script>
@endpush
@endsection

