@extends('admin.layout')

@section('title', 'Material Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Material Categories</h1>
    <button onclick="openCreateMaterialCategoryModal()" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> <span class="material-category-btn-text">Add Category</span>
    </button>
</div>

<div class="card">
    <div class="card-header">
        <strong>Category List</strong>
    </div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="text-end text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                    <tr data-category-id="{{ $category->id }}">
                        <td>{{ ($categories->currentPage() - 1) * $categories->perPage() + $loop->iteration }}</td>
                        <td>{{ $category->name }}</td>
                        <td>{{ Str::limit($category->description, 80) }}</td>
                        <td>
                            <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end text-nowrap">
                                <button onclick="openEditMaterialCategoryModal({{ $category->id }})" class="btn btn-sm btn-outline-warning material-category-action-btn" title="Edit">
                                    <i class="bi bi-pencil me-1"></i><span class="btn-text">Edit</span>
                                </button>
                                <button onclick="showDeleteMaterialCategoryConfirmation({{ $category->id }}, '{{ addslashes($category->name) }}')" class="btn btn-sm btn-outline-danger material-category-action-btn" title="Delete">
                                    <i class="bi bi-trash me-1"></i><span class="btn-text">Delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-3">No categories found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$categories" wrapper-class="card-footer" />
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteMaterialCategoryConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Category</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-material-category-name"></span>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeleteMaterialCategoryConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeleteMaterialCategory()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="materialCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="material-category-modal-title">Add Material Category</h3>
            <button onclick="closeMaterialCategoryModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="materialCategoryForm" onsubmit="submitMaterialCategoryForm(event)">
                @csrf
                <input type="hidden" name="_method" id="material-category-method" value="POST">
                
                <div class="mb-4">
                    <label for="material-category-name" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="material-category-name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                </div>

                <div class="mb-4">
                    <label for="material-category-description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="material-category-description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" id="material-category-is-active" value="1" checked
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeMaterialCategoryModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200" id="material-category-submit-btn">
                        Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .material-category-btn-text {
            display: none;
        }
        .material-category-action-btn .btn-text {
            display: none;
        }
        .material-category-action-btn i {
            margin-right: 0 !important;
        }
        .material-category-action-btn {
            padding: 0.4rem 0.5rem !important;
            min-width: 36px;
        }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentMaterialCategoryId = null;
let deleteMaterialCategoryId = null;

function openCreateMaterialCategoryModal() {
    currentMaterialCategoryId = null;
    const modal = document.getElementById('materialCategoryModal');
    const title = document.getElementById('material-category-modal-title');
    const form = document.getElementById('materialCategoryForm');
    const methodInput = document.getElementById('material-category-method');
    const submitBtn = document.getElementById('material-category-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Add Material Category';
    methodInput.value = 'POST';
    submitBtn.textContent = 'Save Category';
    submitBtn.disabled = false;
    form.reset();
    document.getElementById('material-category-is-active').checked = true;
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
}

function openEditMaterialCategoryModal(categoryId) {
    currentMaterialCategoryId = categoryId;
    const modal = document.getElementById('materialCategoryModal');
    const title = document.getElementById('material-category-modal-title');
    const form = document.getElementById('materialCategoryForm');
    const methodInput = document.getElementById('material-category-method');
    const submitBtn = document.getElementById('material-category-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Edit Material Category';
    methodInput.value = 'PUT';
    submitBtn.textContent = 'Update Category';
    submitBtn.disabled = false;
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    fetch(`/admin/material-categories/${categoryId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('material-category-name').value = data.category.name || '';
        document.getElementById('material-category-description').value = data.category.description || '';
        document.getElementById('material-category-is-active').checked = data.category.is_active || false;
    })
    .catch(error => {
        console.error('Error loading category:', error);
        showNotification('Failed to load category data', 'error');
    });
}

function submitMaterialCategoryForm(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('material-category-submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    formData.set('_token', csrfToken);
    const url = currentMaterialCategoryId 
        ? `/admin/material-categories/${currentMaterialCategoryId}`
        : '/admin/material-categories';
    
    if (currentMaterialCategoryId) {
        formData.set('_method', 'PUT');
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
            submitBtn.disabled = false;
            submitBtn.textContent = currentMaterialCategoryId ? 'Update Category' : 'Save Category';
            closeMaterialCategoryModal();
            
            if (currentMaterialCategoryId) {
                updateMaterialCategoryRow(data.category);
            } else {
                addMaterialCategoryRow(data.category);
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

function closeMaterialCategoryModal() {
    document.getElementById('materialCategoryModal').classList.add('hidden');
    currentMaterialCategoryId = null;
    document.getElementById('materialCategoryForm').reset();
}

function renumberMaterialCategorySerials() {
    const rows = document.querySelectorAll('table tbody tr[data-category-id]');
    rows.forEach((row, idx) => {
        const firstTd = row.querySelector('td');
        if (firstTd) firstTd.textContent = idx + 1;
    });
}

function addMaterialCategoryRow(category) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = tbody.querySelector('tr td[colspan]');
    
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    const row = document.createElement('tr');
    row.setAttribute('data-category-id', category.id);
    row.innerHTML = `
        <td>1</td>
        <td>${category.name}</td>
        <td>${(category.description || '').substring(0, 80)}</td>
        <td>
            <span class="badge ${category.is_active ? 'bg-success' : 'bg-secondary'}">
                ${category.is_active ? 'Active' : 'Inactive'}
            </span>
        </td>
        <td class="text-end">
            <div class="d-flex gap-1 justify-content-end text-nowrap">
                <button onclick="openEditMaterialCategoryModal(${category.id})" class="btn btn-sm btn-outline-warning material-category-action-btn" title="Edit">
                    <i class="bi bi-pencil me-1"></i><span class="btn-text">Edit</span>
                </button>
                <button onclick="showDeleteMaterialCategoryConfirmation(${category.id}, '${(category.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger material-category-action-btn" title="Delete">
                    <i class="bi bi-trash me-1"></i><span class="btn-text">Delete</span>
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
    renumberMaterialCategorySerials();
}

function updateMaterialCategoryRow(category) {
    const row = document.querySelector(`tr[data-category-id="${category.id}"]`);
    if (row) {
        const serial = Array.from(document.querySelectorAll('table tbody tr[data-category-id]')).findIndex(r => r.getAttribute('data-category-id') == category.id) + 1;
        row.innerHTML = `
            <td>${serial || 1}</td>
            <td>${category.name}</td>
            <td>${(category.description || '').substring(0, 80)}</td>
            <td>
                <span class="badge ${category.is_active ? 'bg-success' : 'bg-secondary'}">
                    ${category.is_active ? 'Active' : 'Inactive'}
                </span>
            </td>
            <td class="text-end">
                <div class="d-flex gap-1 justify-content-end text-nowrap">
                    <button onclick="openEditMaterialCategoryModal(${category.id})" class="btn btn-sm btn-outline-warning material-category-action-btn" title="Edit">
                        <i class="bi bi-pencil me-1"></i><span class="btn-text">Edit</span>
                    </button>
                    <button onclick="showDeleteMaterialCategoryConfirmation(${category.id}, '${(category.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger material-category-action-btn" title="Delete">
                        <i class="bi bi-trash me-1"></i><span class="btn-text">Delete</span>
                    </button>
                </div>
            </td>
        `;
    }
}

function showDeleteMaterialCategoryConfirmation(categoryId, categoryName) {
    deleteMaterialCategoryId = categoryId;
    document.getElementById('delete-material-category-name').textContent = categoryName;
    document.getElementById('deleteMaterialCategoryConfirmationModal').classList.remove('hidden');
}

function closeDeleteMaterialCategoryConfirmation() {
    document.getElementById('deleteMaterialCategoryConfirmationModal').classList.add('hidden');
    deleteMaterialCategoryId = null;
}

function confirmDeleteMaterialCategory() {
    if (!deleteMaterialCategoryId) return;
    
    const categoryIdToDelete = deleteMaterialCategoryId;
    const row = document.querySelector(`tr[data-category-id="${categoryIdToDelete}"]`);
    const deleteBtn = event.target;
    
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/material-categories/${categoryIdToDelete}`, {
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
            closeDeleteMaterialCategoryConfirmation();
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
                                <td colspan="5" class="text-center text-muted py-3">No categories found.</td>
                            </tr>
                        `;
                    } else {
                        renumberMaterialCategorySerials();
                    }
                }, 300);
            }
        } else {
            showNotification(data.message || 'Failed to delete category', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error deleting category:', error);
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
        if (!document.getElementById('materialCategoryModal').classList.contains('hidden')) {
            closeMaterialCategoryModal();
        }
        if (!document.getElementById('deleteMaterialCategoryConfirmationModal').classList.contains('hidden')) {
            closeDeleteMaterialCategoryConfirmation();
        }
    }
});

document.getElementById('materialCategoryModal').addEventListener('click', function(e) {
    if (e.target === this) closeMaterialCategoryModal();
});

document.getElementById('deleteMaterialCategoryConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteMaterialCategoryConfirmation();
});
</script>
@endpush
@endsection
