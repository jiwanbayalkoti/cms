@extends('admin.layout')
@section('title', 'Payment Types')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Payment Types</h1>
    <button onclick="openCreatePaymentTypeModal()" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> <span class="payment-type-btn-text">Add Payment Type</span>
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
                        <th>Code</th>
                        <th class="text-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($paymentTypes as $type)
                    <tr data-payment-type-id="{{ $type->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $type->name }}</td>
                        <td>{{ $type->code ?? '-' }}</td>
                        <td>
                            <div class="d-flex gap-1 text-nowrap">
                                <button onclick="openEditPaymentTypeModal({{ $type->id }})" class="btn btn-sm btn-warning" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="showDeletePaymentTypeConfirmation({{ $type->id }}, '{{ addslashes($type->name) }}')" class="btn btn-sm btn-danger" title="Delete">
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
<div id="deletePaymentTypeConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Payment Type</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-payment-type-name"></span>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeletePaymentTypeConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeletePaymentType()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="paymentTypeModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="payment-type-modal-title">Add Payment Type</h3>
            <button onclick="closePaymentTypeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="paymentTypeForm" onsubmit="submitPaymentTypeForm(event)">
                @csrf
                <input type="hidden" name="_method" id="payment-type-method" value="POST">
                
                <div class="mb-4">
                    <label for="payment-type-name" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="payment-type-name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                </div>

                <div class="mb-4">
                    <label for="payment-type-code" class="block text-sm font-medium text-gray-700 mb-2">Code (Optional)</label>
                    <input type="text" name="code" id="payment-type-code"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           placeholder="e.g., vehicle_rent, material_payment">
                    <small class="text-gray-500">Used for backward compatibility with existing data</small>
                    <div class="field-error text-red-600 text-sm mt-1" data-field="code" style="display: none;"></div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closePaymentTypeModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200" id="payment-type-submit-btn">
                        Add
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .payment-type-btn-text {
            display: none;
        }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentPaymentTypeId = null;
let deletePaymentTypeId = null;

function openCreatePaymentTypeModal() {
    currentPaymentTypeId = null;
    const modal = document.getElementById('paymentTypeModal');
    const title = document.getElementById('payment-type-modal-title');
    const form = document.getElementById('paymentTypeForm');
    const methodInput = document.getElementById('payment-type-method');
    const submitBtn = document.getElementById('payment-type-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Add Payment Type';
    methodInput.value = 'POST';
    submitBtn.textContent = 'Add';
    form.reset();
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
}

function openEditPaymentTypeModal(paymentTypeId) {
    currentPaymentTypeId = paymentTypeId;
    const modal = document.getElementById('paymentTypeModal');
    const title = document.getElementById('payment-type-modal-title');
    const form = document.getElementById('paymentTypeForm');
    const methodInput = document.getElementById('payment-type-method');
    const submitBtn = document.getElementById('payment-type-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Edit Payment Type';
    methodInput.value = 'PUT';
    submitBtn.textContent = 'Update';
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    fetch(`/admin/payment-types/${paymentTypeId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('payment-type-name').value = data.paymentType.name || '';
        document.getElementById('payment-type-code').value = data.paymentType.code || '';
    })
    .catch(error => {
        console.error('Error loading payment type:', error);
        showNotification('Failed to load payment type data', 'error');
    });
}

function submitPaymentTypeForm(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('payment-type-submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    const url = currentPaymentTypeId 
        ? `/admin/payment-types/${currentPaymentTypeId}`
        : '/admin/payment-types';
    
    if (currentPaymentTypeId) {
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
            closePaymentTypeModal();
            
            if (currentPaymentTypeId) {
                updatePaymentTypeRow(data.paymentType);
            } else {
                addPaymentTypeRow(data.paymentType);
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

function closePaymentTypeModal() {
    document.getElementById('paymentTypeModal').classList.add('hidden');
    currentPaymentTypeId = null;
    document.getElementById('paymentTypeForm').reset();
}

function addPaymentTypeRow(paymentType) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = tbody.querySelector('tr td[colspan]');
    
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    const row = document.createElement('tr');
    row.setAttribute('data-payment-type-id', paymentType.id);
    row.innerHTML = `
        <td>${paymentType.name}</td>
        <td>${paymentType.code || '-'}</td>
        <td>
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openEditPaymentTypeModal(${paymentType.id})" class="btn btn-sm btn-warning" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeletePaymentTypeConfirmation(${paymentType.id}, '${(paymentType.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
}

function updatePaymentTypeRow(paymentType) {
    const row = document.querySelector(`tr[data-payment-type-id="${paymentType.id}"]`);
    if (row) {
        row.innerHTML = `
            <td>${paymentType.name}</td>
            <td>${paymentType.code || '-'}</td>
            <td>
                <div class="d-flex gap-1 text-nowrap">
                    <button onclick="openEditPaymentTypeModal(${paymentType.id})" class="btn btn-sm btn-warning" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button onclick="showDeletePaymentTypeConfirmation(${paymentType.id}, '${(paymentType.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
    }
}

function showDeletePaymentTypeConfirmation(paymentTypeId, paymentTypeName) {
    deletePaymentTypeId = paymentTypeId;
    document.getElementById('delete-payment-type-name').textContent = paymentTypeName;
    document.getElementById('deletePaymentTypeConfirmationModal').classList.remove('hidden');
}

function closeDeletePaymentTypeConfirmation() {
    document.getElementById('deletePaymentTypeConfirmationModal').classList.add('hidden');
    deletePaymentTypeId = null;
}

function confirmDeletePaymentType() {
    if (!deletePaymentTypeId) return;
    
    const paymentTypeIdToDelete = deletePaymentTypeId;
    const row = document.querySelector(`tr[data-payment-type-id="${paymentTypeIdToDelete}"]`);
    const deleteBtn = event.target;
    
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/payment-types/${paymentTypeIdToDelete}`, {
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
            closeDeletePaymentTypeConfirmation();
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
            showNotification(data.message || 'Failed to delete payment type', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error deleting payment type:', error);
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
        if (!document.getElementById('paymentTypeModal').classList.contains('hidden')) {
            closePaymentTypeModal();
        }
        if (!document.getElementById('deletePaymentTypeConfirmationModal').classList.contains('hidden')) {
            closeDeletePaymentTypeConfirmation();
        }
    }
});

document.getElementById('paymentTypeModal').addEventListener('click', function(e) {
    if (e.target === this) closePaymentTypeModal();
});

document.getElementById('deletePaymentTypeConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeletePaymentTypeConfirmation();
});
</script>
@endpush
@endsection
