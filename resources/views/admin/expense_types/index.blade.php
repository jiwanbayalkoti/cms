@extends('admin.layout')
@section('title', 'Expense Types')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Expense Types</h1>
    <button onclick="openCreateExpenseTypeModal()" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> <span class="expense-type-btn-text">Add Type</span>
    </button>
</div>
<div class="card">
    <div class="card-body p-0">
        <div class="overflow-x-auto">
            <table class="table table-bordered mb-0">
                <thead>
                    <tr>
                        <th>SN</th>
                        <th>Name</th>
                        <th class="text-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($expenseTypes as $type)
                    <tr data-expense-type-id="{{ $type->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $type->name }}</td>
                        <td>
                            <div class="d-flex gap-1 text-nowrap">
                                <button onclick="openEditExpenseTypeModal({{ $type->id }})" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="showDeleteExpenseTypeConfirmation({{ $type->id }}, '{{ addslashes($type->name) }}')" class="btn btn-sm btn-danger" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteExpenseTypeConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Expense Type</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-expense-type-name"></span>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeleteExpenseTypeConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeleteExpenseType()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="expenseTypeModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="expense-type-modal-title">Add Expense Type</h3>
            <button onclick="closeExpenseTypeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="expenseTypeForm" onsubmit="submitExpenseTypeForm(event)">
                @csrf
                <input type="hidden" name="_method" id="expense-type-method" value="POST">
                
                <div class="mb-4">
                    <label for="expense-type-name" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="expense-type-name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closeExpenseTypeModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200" id="expense-type-submit-btn">
                        Add
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .expense-type-btn-text {
            display: none;
        }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentExpenseTypeId = null;
let deleteExpenseTypeId = null;

function openCreateExpenseTypeModal() {
    currentExpenseTypeId = null;
    const modal = document.getElementById('expenseTypeModal');
    const title = document.getElementById('expense-type-modal-title');
    const form = document.getElementById('expenseTypeForm');
    const methodInput = document.getElementById('expense-type-method');
    const submitBtn = document.getElementById('expense-type-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Add Expense Type';
    methodInput.value = 'POST';
    submitBtn.textContent = 'Add';
    form.reset();
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
}

function openEditExpenseTypeModal(expenseTypeId) {
    currentExpenseTypeId = expenseTypeId;
    const modal = document.getElementById('expenseTypeModal');
    const title = document.getElementById('expense-type-modal-title');
    const form = document.getElementById('expenseTypeForm');
    const methodInput = document.getElementById('expense-type-method');
    const submitBtn = document.getElementById('expense-type-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Edit Expense Type';
    methodInput.value = 'PUT';
    submitBtn.textContent = 'Update';
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    fetch(`/admin/expense-types/${expenseTypeId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('expense-type-name').value = data.expenseType.name || '';
    })
    .catch(error => {
        console.error('Error loading expense type:', error);
        showNotification('Failed to load expense type data', 'error');
    });
}

function submitExpenseTypeForm(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('expense-type-submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    const url = currentExpenseTypeId 
        ? `/admin/expense-types/${currentExpenseTypeId}`
        : '/admin/expense-types';
    
    if (currentExpenseTypeId) {
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
            closeExpenseTypeModal();
            
            if (currentExpenseTypeId) {
                updateExpenseTypeRow(data.expenseType);
            } else {
                addExpenseTypeRow(data.expenseType);
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

function closeExpenseTypeModal() {
    document.getElementById('expenseTypeModal').classList.add('hidden');
    currentExpenseTypeId = null;
    document.getElementById('expenseTypeForm').reset();
}

function addExpenseTypeRow(expenseType) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = tbody.querySelector('tr td[colspan]');
    
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    const row = document.createElement('tr');
    row.setAttribute('data-expense-type-id', expenseType.id);
    row.innerHTML = `
        <td>${expenseType.name}</td>
        <td>
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openEditExpenseTypeModal(${expenseType.id})" class="btn btn-sm btn-warning" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeleteExpenseTypeConfirmation(${expenseType.id}, '${(expenseType.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
}

function updateExpenseTypeRow(expenseType) {
    const row = document.querySelector(`tr[data-expense-type-id="${expenseType.id}"]`);
    if (row) {
        row.innerHTML = `
            <td>${expenseType.name}</td>
            <td>
                <div class="d-flex gap-1 text-nowrap">
                    <button onclick="openEditExpenseTypeModal(${expenseType.id})" class="btn btn-sm btn-warning" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button onclick="showDeleteExpenseTypeConfirmation(${expenseType.id}, '${(expenseType.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
    }
}

function showDeleteExpenseTypeConfirmation(expenseTypeId, expenseTypeName) {
    deleteExpenseTypeId = expenseTypeId;
    document.getElementById('delete-expense-type-name').textContent = expenseTypeName;
    document.getElementById('deleteExpenseTypeConfirmationModal').classList.remove('hidden');
}

function closeDeleteExpenseTypeConfirmation() {
    document.getElementById('deleteExpenseTypeConfirmationModal').classList.add('hidden');
    deleteExpenseTypeId = null;
}

function confirmDeleteExpenseType() {
    if (!deleteExpenseTypeId) return;
    
    const expenseTypeIdToDelete = deleteExpenseTypeId;
    const row = document.querySelector(`tr[data-expense-type-id="${expenseTypeIdToDelete}"]`);
    const deleteBtn = event.target;
    
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/expense-types/${expenseTypeIdToDelete}`, {
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
            closeDeleteExpenseTypeConfirmation();
            showNotification(data.message, 'success');
            
            if (row) {
                row.style.transition = 'opacity 0.3s, transform 0.3s';
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                setTimeout(() => {
                    row.remove();
                }, 300);
            }
        } else {
            showNotification(data.message || 'Failed to delete expense type', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error deleting expense type:', error);
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
        if (!document.getElementById('expenseTypeModal').classList.contains('hidden')) {
            closeExpenseTypeModal();
        }
        if (!document.getElementById('deleteExpenseTypeConfirmationModal').classList.contains('hidden')) {
            closeDeleteExpenseTypeConfirmation();
        }
    }
});

document.getElementById('expenseTypeModal').addEventListener('click', function(e) {
    if (e.target === this) closeExpenseTypeModal();
});

document.getElementById('deleteExpenseTypeConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteExpenseTypeConfirmation();
});
</script>
@endpush
@endsection
