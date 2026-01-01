@extends('admin.layout')
@section('title', 'Material Names')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Material Names</h1>
    <button onclick="openCreateMaterialNameModal()" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> <span class="material-name-btn-text">Add Material Name</span>
    </button>
</div>
<div class="card">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th class="text-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($materialNames as $materialName)
                        <tr data-material-name-id="{{ $materialName->id }}">
                            <td>{{ $materialName->id }}</td>
                            <td>{{ $materialName->name }}</td>
                            <td>
                                <div class="d-flex gap-1 text-nowrap">
                                    <button onclick="openViewMaterialNameModal({{ $materialName->id }})" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button onclick="openEditMaterialNameModal({{ $materialName->id }})" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button onclick="showDeleteMaterialNameConfirmation({{ $materialName->id }}, '{{ addslashes($materialName->name) }}')" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No material names found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($materialNames->hasPages())
            <div class="mt-3">
                <x-pagination :paginator="$materialNames" />
            </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteMaterialNameConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Material Name</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-material-name-name"></span>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeleteMaterialNameConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeleteMaterialName()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="materialNameModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="material-name-modal-title">Add Material Name</h3>
            <button onclick="closeMaterialNameModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="materialNameForm" onsubmit="submitMaterialNameForm(event)">
                @csrf
                <input type="hidden" name="_method" id="material-name-method" value="POST">
                
                <div class="mb-4">
                    <label for="material-name-name" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="material-name-name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeMaterialNameModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200" id="material-name-submit-btn">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="viewMaterialNameModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Material Name Details</h3>
            <button onclick="closeViewMaterialNameModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6" id="view-material-name-content">
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
        .material-name-btn-text {
            display: none;
        }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentMaterialNameId = null;
let deleteMaterialNameId = null;

function openCreateMaterialNameModal() {
    currentMaterialNameId = null;
    const modal = document.getElementById('materialNameModal');
    const title = document.getElementById('material-name-modal-title');
    const form = document.getElementById('materialNameForm');
    const methodInput = document.getElementById('material-name-method');
    const submitBtn = document.getElementById('material-name-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Add Material Name';
    methodInput.value = 'POST';
    submitBtn.textContent = 'Save';
    form.reset();
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
}

function openEditMaterialNameModal(materialNameId) {
    currentMaterialNameId = materialNameId;
    const modal = document.getElementById('materialNameModal');
    const title = document.getElementById('material-name-modal-title');
    const form = document.getElementById('materialNameForm');
    const methodInput = document.getElementById('material-name-method');
    const submitBtn = document.getElementById('material-name-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Edit Material Name';
    methodInput.value = 'PUT';
    submitBtn.textContent = 'Update';
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    fetch(`/admin/material-names/${materialNameId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('material-name-name').value = data.materialName.name || '';
    })
    .catch(error => {
        console.error('Error loading material name:', error);
        showNotification('Failed to load material name data', 'error');
    });
}

function openViewMaterialNameModal(materialNameId) {
    const modal = document.getElementById('viewMaterialNameModal');
    const content = document.getElementById('view-material-name-content');
    
    modal.classList.remove('hidden');
    content.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    
    fetch(`/admin/material-names/${materialNameId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const mn = data.materialName;
        content.innerHTML = `
            <div class="space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">ID</dt>
                    <dd class="mt-1 text-sm text-gray-900">${mn.id || ''}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">${mn.name || ''}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created At</dt>
                    <dd class="mt-1 text-sm text-gray-900">${mn.created_at || ''}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Updated At</dt>
                    <dd class="mt-1 text-sm text-gray-900">${mn.updated_at || ''}</dd>
                </div>
            </div>
            <div class="mt-4 flex justify-end gap-2">
                <button onclick="closeViewMaterialNameModal(); openEditMaterialNameModal(${mn.id})" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
                    Edit
                </button>
                <button onclick="closeViewMaterialNameModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    })
    .catch(error => {
        console.error('Error loading material name:', error);
        content.innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-600 mb-4">Failed to load material name details</p>
                <button onclick="closeViewMaterialNameModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    });
}

function closeViewMaterialNameModal() {
    document.getElementById('viewMaterialNameModal').classList.add('hidden');
}

function submitMaterialNameForm(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('material-name-submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    const url = currentMaterialNameId 
        ? `/admin/material-names/${currentMaterialNameId}`
        : '/admin/material-names';
    
    if (currentMaterialNameId) {
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
            showNotification(data.message, 'success');
            closeMaterialNameModal();
            
            if (currentMaterialNameId) {
                updateMaterialNameRow(data.materialName);
            } else {
                addMaterialNameRow(data.materialName);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (error.errors) {
            Object.keys(error.errors).forEach(field => {
                const errorEl = document.querySelector(`.field-error[data-field="${field}"]`);
                if (errorEl) {
                    errorEl.textContent = error.errors[field][0];
                    errorEl.style.display = 'block';
                }
            });
        }
        showNotification(error.message || 'Validation failed', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

function closeMaterialNameModal() {
    document.getElementById('materialNameModal').classList.add('hidden');
    currentMaterialNameId = null;
    document.getElementById('materialNameForm').reset();
}

function addMaterialNameRow(materialName) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = tbody.querySelector('tr td[colspan]');
    
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    const row = document.createElement('tr');
    row.setAttribute('data-material-name-id', materialName.id);
    row.innerHTML = `
        <td>${materialName.id}</td>
        <td>${materialName.name}</td>
        <td>
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewMaterialNameModal(${materialName.id})" class="btn btn-sm btn-outline-primary" title="View">
                    <i class="bi bi-eye"></i>
                </button>
                <button onclick="openEditMaterialNameModal(${materialName.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeleteMaterialNameConfirmation(${materialName.id}, '${(materialName.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
}

function updateMaterialNameRow(materialName) {
    const row = document.querySelector(`tr[data-material-name-id="${materialName.id}"]`);
    if (row) {
        row.innerHTML = `
            <td>${materialName.id}</td>
            <td>${materialName.name}</td>
            <td>
                <div class="d-flex gap-1 text-nowrap">
                    <button onclick="openViewMaterialNameModal(${materialName.id})" class="btn btn-sm btn-outline-primary" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button onclick="openEditMaterialNameModal(${materialName.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button onclick="showDeleteMaterialNameConfirmation(${materialName.id}, '${(materialName.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
    }
}

function showDeleteMaterialNameConfirmation(materialNameId, materialNameName) {
    deleteMaterialNameId = materialNameId;
    document.getElementById('delete-material-name-name').textContent = materialNameName;
    document.getElementById('deleteMaterialNameConfirmationModal').classList.remove('hidden');
}

function closeDeleteMaterialNameConfirmation() {
    document.getElementById('deleteMaterialNameConfirmationModal').classList.add('hidden');
    deleteMaterialNameId = null;
}

function confirmDeleteMaterialName() {
    if (!deleteMaterialNameId) return;
    
    const materialNameIdToDelete = deleteMaterialNameId;
    const row = document.querySelector(`tr[data-material-name-id="${materialNameIdToDelete}"]`);
    const deleteBtn = event.target;
    
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/material-names/${materialNameIdToDelete}`, {
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
            closeDeleteMaterialNameConfirmation();
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
                            <tr><td colspan="3" class="text-center text-muted py-3">No material names found.</td></tr>
                        `;
                    }
                }, 300);
            }
        } else {
            showNotification(data.message || 'Failed to delete material name', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error deleting material name:', error);
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
        if (!document.getElementById('materialNameModal').classList.contains('hidden')) {
            closeMaterialNameModal();
        }
        if (!document.getElementById('viewMaterialNameModal').classList.contains('hidden')) {
            closeViewMaterialNameModal();
        }
        if (!document.getElementById('deleteMaterialNameConfirmationModal').classList.contains('hidden')) {
            closeDeleteMaterialNameConfirmation();
        }
    }
});

document.getElementById('materialNameModal').addEventListener('click', function(e) {
    if (e.target === this) closeMaterialNameModal();
});

document.getElementById('viewMaterialNameModal').addEventListener('click', function(e) {
    if (e.target === this) closeViewMaterialNameModal();
});

document.getElementById('deleteMaterialNameConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteMaterialNameConfirmation();
});
</script>
@endpush
@endsection
