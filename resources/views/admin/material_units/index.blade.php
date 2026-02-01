@extends('admin.layout')

@section('title', 'Material Units')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Material Units</h1>
    <button onclick="openCreateMaterialUnitModal()" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> <span class="material-unit-btn-text">Add Unit</span>
    </button>
</div>

<div class="card">
    <div class="card-header">
        <strong>Unit List</strong>
    </div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>SN</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th class="text-end text-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $unit)
                    <tr data-unit-id="{{ $unit->id }}">
                        <td>{{ $unit->id }}</td>
                        <td>{{ $unit->name }}</td>
                        <td>{{ Str::limit($unit->description, 80) }}</td>
                        <td class="text-end">
                            <div class="d-flex gap-1 justify-content-end text-nowrap">
                                <button onclick="openEditMaterialUnitModal({{ $unit->id }})" class="btn btn-sm btn-outline-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="showDeleteMaterialUnitConfirmation({{ $unit->id }}, '{{ addslashes($unit->name) }}')" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-3">No units found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <x-pagination :paginator="$units" wrapper-class="card-footer" />
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteMaterialUnitConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Unit</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-material-unit-name"></span>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeleteMaterialUnitConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeleteMaterialUnit()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="materialUnitModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="material-unit-modal-title">Add Material Unit</h3>
            <button onclick="closeMaterialUnitModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="materialUnitForm" onsubmit="submitMaterialUnitForm(event)">
                @csrf
                <input type="hidden" name="_method" id="material-unit-method" value="POST">
                
                <div class="mb-4">
                    <label for="material-unit-name" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="material-unit-name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                </div>

                <div class="mb-4">
                    <label for="material-unit-description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <textarea name="description" id="material-unit-description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeMaterialUnitModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200" id="material-unit-submit-btn">
                        Save Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .material-unit-btn-text {
            display: none;
        }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentMaterialUnitId = null;
let deleteMaterialUnitId = null;

function openCreateMaterialUnitModal() {
    currentMaterialUnitId = null;
    const modal = document.getElementById('materialUnitModal');
    const title = document.getElementById('material-unit-modal-title');
    const form = document.getElementById('materialUnitForm');
    const methodInput = document.getElementById('material-unit-method');
    const submitBtn = document.getElementById('material-unit-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Add Material Unit';
    methodInput.value = 'POST';
    submitBtn.textContent = 'Save Unit';
    form.reset();
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
}

function openEditMaterialUnitModal(unitId) {
    currentMaterialUnitId = unitId;
    const modal = document.getElementById('materialUnitModal');
    const title = document.getElementById('material-unit-modal-title');
    const form = document.getElementById('materialUnitForm');
    const methodInput = document.getElementById('material-unit-method');
    const submitBtn = document.getElementById('material-unit-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Edit Material Unit';
    methodInput.value = 'PUT';
    submitBtn.textContent = 'Update Unit';
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    fetch(`/admin/material-units/${unitId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('material-unit-name').value = data.unit.name || '';
        document.getElementById('material-unit-description').value = data.unit.description || '';
    })
    .catch(error => {
        console.error('Error loading unit:', error);
        showNotification('Failed to load unit data', 'error');
    });
}

function submitMaterialUnitForm(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('material-unit-submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    const url = currentMaterialUnitId 
        ? `/admin/material-units/${currentMaterialUnitId}`
        : '/admin/material-units';
    
    if (currentMaterialUnitId) {
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
            closeMaterialUnitModal();
            
            if (currentMaterialUnitId) {
                updateMaterialUnitRow(data.unit);
            } else {
                addMaterialUnitRow(data.unit);
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

function closeMaterialUnitModal() {
    document.getElementById('materialUnitModal').classList.add('hidden');
    currentMaterialUnitId = null;
    document.getElementById('materialUnitForm').reset();
}

function addMaterialUnitRow(unit) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = tbody.querySelector('tr td[colspan]');
    
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    const row = document.createElement('tr');
    row.setAttribute('data-unit-id', unit.id);
    row.innerHTML = `
        <td>1</td>
        <td>${unit.name}</td>
        <td>${(unit.description || '').substring(0, 80)}</td>
        <td class="text-end">
            <div class="d-flex gap-1 justify-content-end text-nowrap">
                <button onclick="openEditMaterialUnitModal(${unit.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeleteMaterialUnitConfirmation(${unit.id}, '${(unit.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
    renumberMaterialUnitSerials();
}

function renumberMaterialUnitSerials() {
    const rows = document.querySelectorAll('table tbody tr[data-unit-id]');
    rows.forEach((row, idx) => {
        const firstTd = row.querySelector('td');
        if (firstTd) firstTd.textContent = idx + 1;
    });
}

function updateMaterialUnitRow(unit) {
    const row = document.querySelector(`tr[data-unit-id="${unit.id}"]`);
    if (row) {
        const serial = Array.from(document.querySelectorAll('table tbody tr[data-unit-id]')).findIndex(r => r.getAttribute('data-unit-id') == unit.id) + 1;
        row.innerHTML = `
            <td>${serial || 1}</td>
            <td>${unit.name}</td>
            <td>${(unit.description || '').substring(0, 80)}</td>
            <td class="text-end">
                <div class="d-flex gap-1 justify-content-end text-nowrap">
                    <button onclick="openEditMaterialUnitModal(${unit.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button onclick="showDeleteMaterialUnitConfirmation(${unit.id}, '${(unit.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
    }
}

function showDeleteMaterialUnitConfirmation(unitId, unitName) {
    deleteMaterialUnitId = unitId;
    document.getElementById('delete-material-unit-name').textContent = unitName;
    document.getElementById('deleteMaterialUnitConfirmationModal').classList.remove('hidden');
}

function closeDeleteMaterialUnitConfirmation() {
    document.getElementById('deleteMaterialUnitConfirmationModal').classList.add('hidden');
    deleteMaterialUnitId = null;
}

function confirmDeleteMaterialUnit() {
    if (!deleteMaterialUnitId) return;
    
    const unitIdToDelete = deleteMaterialUnitId;
    const row = document.querySelector(`tr[data-unit-id="${unitIdToDelete}"]`);
    const deleteBtn = event.target;
    
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/material-units/${unitIdToDelete}`, {
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
            closeDeleteMaterialUnitConfirmation();
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
                                <td colspan="4" class="text-center text-muted py-3">No units found.</td>
                            </tr>
                        `;
                    } else {
                        renumberMaterialUnitSerials();
                    }
                }, 300);
            }
        } else {
            showNotification(data.message || 'Failed to delete unit', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error deleting unit:', error);
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
        if (!document.getElementById('materialUnitModal').classList.contains('hidden')) {
            closeMaterialUnitModal();
        }
        if (!document.getElementById('deleteMaterialUnitConfirmationModal').classList.contains('hidden')) {
            closeDeleteMaterialUnitConfirmation();
        }
    }
});

document.getElementById('materialUnitModal').addEventListener('click', function(e) {
    if (e.target === this) closeMaterialUnitModal();
});

document.getElementById('deleteMaterialUnitConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteMaterialUnitConfirmation();
});
</script>
@endpush
@endsection
