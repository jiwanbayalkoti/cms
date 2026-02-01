@extends('admin.layout')
@section('title', 'Payment Modes')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Payment Modes</h1>
    <button onclick="openCreatePaymentModeModal()" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> <span class="payment-mode-btn-text">Add Payment Mode</span>
    </button>
</div>
<div class="card">
    <div class="card-body">
        <div class="overflow-x-auto">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>SN</th>
                        <th>Name</th>
                        <th class="text-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paymentModes as $paymentMode)
                        <tr data-payment-mode-id="{{ $paymentMode->id }}">
                            <td>{{ ($paymentModes->currentPage() - 1) * $paymentModes->perPage() + $loop->iteration }}</td>
                            <td>{{ $paymentMode->name }}</td>
                            <td>
                                <div class="d-flex gap-1 text-nowrap">
                                    <button onclick="openEditPaymentModeModal({{ $paymentMode->id }})" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button onclick="showDeletePaymentModeConfirmation({{ $paymentMode->id }}, '{{ addslashes($paymentMode->name) }}')" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-3">No payment modes found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($paymentModes->hasPages())
            <div class="mt-3">
                <x-pagination :paginator="$paymentModes" />
            </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deletePaymentModeConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Payment Mode</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-payment-mode-name"></span>? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeletePaymentModeConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeletePaymentMode()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="paymentModeModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="payment-mode-modal-title">Add Payment Mode</h3>
            <button onclick="closePaymentModeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <form id="paymentModeForm" onsubmit="submitPaymentModeForm(event)">
                @csrf
                <input type="hidden" name="_method" id="payment-mode-method" value="POST">
                
                <div class="mb-4">
                    <label for="payment-mode-name" class="block text-sm font-medium text-gray-700 mb-2">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="payment-mode-name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <div class="field-error text-red-600 text-sm mt-1" data-field="name" style="display: none;"></div>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <button type="button" onclick="closePaymentModeModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition duration-200">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-200" id="payment-mode-submit-btn">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .payment-mode-btn-text {
            display: none;
        }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentPaymentModeId = null;
let deletePaymentModeId = null;

function openCreatePaymentModeModal() {
    currentPaymentModeId = null;
    const modal = document.getElementById('paymentModeModal');
    const title = document.getElementById('payment-mode-modal-title');
    const form = document.getElementById('paymentModeForm');
    const methodInput = document.getElementById('payment-mode-method');
    const submitBtn = document.getElementById('payment-mode-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Add Payment Mode';
    methodInput.value = 'POST';
    submitBtn.textContent = 'Save';
    form.reset();
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
}

function openEditPaymentModeModal(paymentModeId) {
    currentPaymentModeId = paymentModeId;
    const modal = document.getElementById('paymentModeModal');
    const title = document.getElementById('payment-mode-modal-title');
    const form = document.getElementById('paymentModeForm');
    const methodInput = document.getElementById('payment-mode-method');
    const submitBtn = document.getElementById('payment-mode-submit-btn');
    
    modal.classList.remove('hidden');
    title.textContent = 'Edit Payment Mode';
    methodInput.value = 'PUT';
    submitBtn.textContent = 'Update';
    
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    
    fetch(`/admin/payment-modes/${paymentModeId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('payment-mode-name').value = data.paymentMode.name || '';
    })
    .catch(error => {
        console.error('Error loading payment mode:', error);
        showNotification('Failed to load payment mode data', 'error');
    });
}

function submitPaymentModeForm(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('payment-mode-submit-btn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    const url = currentPaymentModeId 
        ? `/admin/payment-modes/${currentPaymentModeId}`
        : '/admin/payment-modes';
    
    if (currentPaymentModeId) {
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
            closePaymentModeModal();
            
            if (currentPaymentModeId) {
                updatePaymentModeRow(data.paymentMode);
            } else {
                addPaymentModeRow(data.paymentMode);
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

function closePaymentModeModal() {
    document.getElementById('paymentModeModal').classList.add('hidden');
    currentPaymentModeId = null;
    document.getElementById('paymentModeForm').reset();
}

function addPaymentModeRow(paymentMode) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = tbody.querySelector('tr td[colspan]');
    
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    const row = document.createElement('tr');
    row.setAttribute('data-payment-mode-id', paymentMode.id);
    row.innerHTML = `
        <td>1</td>
        <td>${paymentMode.name}</td>
        <td>
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openEditPaymentModeModal(${paymentMode.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeletePaymentModeConfirmation(${paymentMode.id}, '${(paymentMode.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
    renumberPaymentModeSerials();
}

function renumberPaymentModeSerials() {
    const rows = document.querySelectorAll('table tbody tr[data-payment-mode-id]');
    rows.forEach((row, idx) => {
        const firstTd = row.querySelector('td');
        if (firstTd) firstTd.textContent = idx + 1;
    });
}

function updatePaymentModeRow(paymentMode) {
    const row = document.querySelector(`tr[data-payment-mode-id="${paymentMode.id}"]`);
    if (row) {
        const serial = Array.from(document.querySelectorAll('table tbody tr[data-payment-mode-id]')).findIndex(r => r.getAttribute('data-payment-mode-id') == paymentMode.id) + 1;
        row.innerHTML = `
            <td>${serial || 1}</td>
            <td>${paymentMode.name}</td>
            <td>
                <div class="d-flex gap-1 text-nowrap">
                    <button onclick="openEditPaymentModeModal(${paymentMode.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button onclick="showDeletePaymentModeConfirmation(${paymentMode.id}, '${(paymentMode.name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
    }
}

function showDeletePaymentModeConfirmation(paymentModeId, paymentModeName) {
    deletePaymentModeId = paymentModeId;
    document.getElementById('delete-payment-mode-name').textContent = paymentModeName;
    document.getElementById('deletePaymentModeConfirmationModal').classList.remove('hidden');
}

function closeDeletePaymentModeConfirmation() {
    document.getElementById('deletePaymentModeConfirmationModal').classList.add('hidden');
    deletePaymentModeId = null;
}

function confirmDeletePaymentMode() {
    if (!deletePaymentModeId) return;
    
    const paymentModeIdToDelete = deletePaymentModeId;
    const row = document.querySelector(`tr[data-payment-mode-id="${paymentModeIdToDelete}"]`);
    const deleteBtn = event.target;
    
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/payment-modes/${paymentModeIdToDelete}`, {
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
            closeDeletePaymentModeConfirmation();
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
                            <tr><td colspan="3" class="text-center text-muted py-3">No payment modes found.</td></tr>
                        `;
                    } else {
                        renumberPaymentModeSerials();
                    }
                }, 300);
            }
        } else {
            showNotification(data.message || 'Failed to delete payment mode', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error deleting payment mode:', error);
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
        if (!document.getElementById('paymentModeModal').classList.contains('hidden')) {
            closePaymentModeModal();
        }
        if (!document.getElementById('deletePaymentModeConfirmationModal').classList.contains('hidden')) {
            closeDeletePaymentModeConfirmation();
        }
    }
});

document.getElementById('paymentModeModal').addEventListener('click', function(e) {
    if (e.target === this) closePaymentModeModal();
});

document.getElementById('deletePaymentModeConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeletePaymentModeConfirmation();
});
</script>
@endpush
@endsection
