@extends('admin.layout')

@section('title', 'Advance Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
<div>
        <h1 class="h3 mb-0">Advance Payments</h1>
        <p class="text-muted mb-0">Manage advance payments for vehicle rent and materials</p>
    </div>
    <button onclick="openCreateAdvancePaymentModal()" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> <span class="advance-payment-btn-text">Add Advance Payment</span>
    </button>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filters</h5>
    </div>
    <div class="card-body">
        <form id="filterForm" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Project</label>
                <select name="project_id" id="filter_project_id" class="form-select form-select-sm" onchange="applyFiltersDebounced()">
                    <option value="" {{ !request('project_id') ? 'selected' : '' }}>All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Payment Type</label>
                <select name="payment_type" id="filter_payment_type" class="form-select form-select-sm" onchange="applyFiltersDebounced()">
                    <option value="" {{ !request('payment_type') ? 'selected' : '' }}>All Types</option>
                    <option value="vehicle_rent" {{ request('payment_type') == 'vehicle_rent' ? 'selected' : '' }}>Vehicle Rent</option>
                    <option value="material_payment" {{ request('payment_type') == 'material_payment' ? 'selected' : '' }}>Material Payment</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Supplier</label>
                <select name="supplier_id" id="filter_supplier_id" class="form-select form-select-sm" onchange="applyFiltersDebounced()">
                    <option value="" {{ !request('supplier_id') ? 'selected' : '' }}>All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Start Date</label>
                <input type="date" name="start_date" id="filter_start_date" class="form-control form-control-sm" value="{{ request('start_date') ?: '' }}" onchange="applyFiltersDebounced()">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">End Date</label>
                <input type="date" name="end_date" id="filter_end_date" class="form-control form-control-sm" value="{{ request('end_date') ?: '' }}" onchange="applyFiltersDebounced()">
            </div>
            <div class="col-md-12 mt-2">
                <button type="button" onclick="resetFilters()" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset Filters
                </button>
                <small class="text-muted ms-2">Filters apply automatically when changed</small>
            </div>
        </form>
    </div>
</div>

<div id="advancePaymentsSummary">
@if($advancePayments->count() > 0)
<div class="card mb-4 shadow-sm">
    <div class="card-body py-3">
            @include('admin.advance_payments.partials.summary', ['totalAmount' => $totalAmount])
        </div>
    </div>
    @endif
</div>

<div id="advance-payments-loading" class="hidden card mb-4">
    <div class="card-body text-center py-5">
        <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-2 text-gray-600">Loading advance payments...</p>
    </div>
</div>

<div id="advance-payments-table-container" class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Reference</th>
                        <th>Project</th>
                        <th>Supplier</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="advancePaymentsTableBody">
                    @forelse($advancePayments as $payment)
                        <tr data-advance-payment-id="{{ $payment->id }}">
                            <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}
                                </span>
                            </td>
                            <td>N/A</td>
                            <td>{{ $payment->project->name ?? 'N/A' }}</td>
                            <td>{{ $payment->supplier->name ?? 'N/A' }}</td>
                            <td><strong>Rs. {{ number_format($payment->amount, 2) }}</strong></td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'N/A')) }}</td>
                            <td>
                                <div class="d-flex gap-1 text-nowrap">
                                    <button onclick="openViewAdvancePaymentModal({{ $payment->id }})" class="btn btn-sm btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button onclick="openEditAdvancePaymentModal({{ $payment->id }})" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button onclick="showDeleteAdvancePaymentConfirmation({{ $payment->id }}, '{{ addslashes($payment->supplier->name ?? 'N/A') }}')" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No advance payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div id="advancePaymentsPagination" class="mt-4">
            <x-pagination :paginator="$advancePayments" :show-info="true" />
        </div>
    </div>
</div>

<!-- Create/Edit Modal -->
<div id="advancePaymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white z-10 border-b px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold" id="advancePaymentModalTitle">Add Advance Payment</h2>
            <button onclick="closeAdvancePaymentModal()" class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        <div class="p-6">
            <form id="advancePaymentForm">
                @csrf
                <input type="hidden" name="_method" id="advancePaymentFormMethod" value="POST">
                <input type="hidden" name="advance_payment_id" id="advancePaymentId">
                
                <div class="row mb-4">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Payment Type <span class="text-danger">*</span></label>
                        <select name="payment_type" id="payment_type" class="form-select">
                            <option value="">Select Payment Type</option>
                        </select>
                        <div class="field-error text-danger small mt-1" data-field="payment_type" style="display: none;"></div>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Project</label>
                        <select name="project_id" id="project_id" class="form-select">
                            <option value="">None</option>
                        </select>
                    </div>
                    
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Supplier <span class="text-danger">*</span></label>
                        <select name="supplier_id" id="supplier_id" class="form-select">
                            <option value="">Select Supplier</option>
                        </select>
                        <div class="field-error text-danger small mt-1" data-field="supplier_id" style="display: none;"></div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Payment Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Payment Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="amount" step="0.01" min="0.01" 
                                       class="form-control" placeholder="0.00">
                                <div class="field-error text-danger small mt-1" data-field="amount" style="display: none;"></div>
                                <small class="text-muted">Amount to be paid in advance</small>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" id="payment_date" class="form-control">
                                <div class="field-error text-danger small mt-1" data-field="payment_date" style="display: none;"></div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bank Account</label>
                                <select name="bank_account_id" id="bank_account_id" class="form-select">
                                    <option value="">None</option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Method</label>
                                <select name="payment_method" id="payment_method" class="form-select">
                                    <option value="">Select Method</option>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="online_payment">Online Payment</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Transaction Reference</label>
                                <input type="text" name="transaction_reference" id="transaction_reference" class="form-control" 
                                       placeholder="Transaction ID, Cheque Number, etc.">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="Additional notes"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end">
                    <button type="button" onclick="closeAdvancePaymentModal()" class="btn btn-secondary me-2">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="advancePaymentSubmitBtn">Create Advance Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Modal -->
<div id="viewAdvancePaymentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-white z-10 border-b px-6 py-4 flex justify-between items-center">
            <h2 class="text-xl font-semibold">Advance Payment Details</h2>
            <button onclick="closeViewAdvancePaymentModal()" class="text-gray-500 hover:text-gray-700">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>
        <div class="p-6" id="viewAdvancePaymentContent">
            <!-- Content will be loaded here -->
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteAdvancePaymentConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <i class="bi bi-exclamation-triangle-fill text-red-600 text-3xl"></i>
            </div>
            <h3 class="text-lg font-semibold text-center mb-2">Delete Advance Payment</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete this advance payment for <strong id="deleteAdvancePaymentName"></strong>? This action cannot be undone.
            </p>
            <div class="flex justify-end gap-3">
                <button onclick="closeDeleteAdvancePaymentConfirmation()" class="btn btn-secondary">Cancel</button>
                <button onclick="confirmDeleteAdvancePayment()" class="btn btn-danger">Delete</button>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 768px) {
    .advance-payment-btn-text {
        display: none;
    }
}
</style>

<script>
let deleteAdvancePaymentId = null;

// Modal functions
function openCreateAdvancePaymentModal() {
    const modal = document.getElementById('advancePaymentModal');
    const form = document.getElementById('advancePaymentForm');
    const title = document.getElementById('advancePaymentModalTitle');
    const submitBtn = document.getElementById('advancePaymentSubmitBtn');
    const methodInput = document.getElementById('advancePaymentFormMethod');
    
    // Reset form
    form.reset();
    methodInput.value = 'POST';
    title.textContent = 'Add Advance Payment';
    submitBtn.textContent = 'Create Advance Payment';
    document.getElementById('advancePaymentId').value = '';
    
    // Clear errors
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    // Fetch form data
    fetch('{{ route("admin.advance-payments.create") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Populate payment types
        const paymentTypeSelect = document.getElementById('payment_type');
        paymentTypeSelect.innerHTML = '<option value="">Select Payment Type</option>';
        data.paymentTypes.forEach(type => {
            const typeCode = type.code || type.name.toLowerCase().replace(/\s+/g, '_');
            paymentTypeSelect.innerHTML += `<option value="${typeCode}">${type.name}</option>`;
        });
        
        // Populate projects
        const projectSelect = document.getElementById('project_id');
        projectSelect.innerHTML = '<option value="">None</option>';
        data.projects.forEach(project => {
            projectSelect.innerHTML += `<option value="${project.id}">${project.name}</option>`;
        });
        
        // Populate suppliers
        const supplierSelect = document.getElementById('supplier_id');
        supplierSelect.innerHTML = '<option value="">Select Supplier</option>';
        data.suppliers.forEach(supplier => {
            supplierSelect.innerHTML += `<option value="${supplier.id}">${supplier.name}</option>`;
        });
        
        // Populate bank accounts
        const bankAccountSelect = document.getElementById('bank_account_id');
        bankAccountSelect.innerHTML = '<option value="">None</option>';
        data.bankAccounts.forEach(account => {
            bankAccountSelect.innerHTML += `<option value="${account.id}">${account.account_name} (${account.account_type})</option>`;
        });
        
        // Set default date
        document.getElementById('payment_date').value = new Date().toISOString().split('T')[0];
        
        modal.classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error loading form data', 'error');
    });
}

function openEditAdvancePaymentModal(id) {
    const modal = document.getElementById('advancePaymentModal');
    const form = document.getElementById('advancePaymentForm');
    const title = document.getElementById('advancePaymentModalTitle');
    const submitBtn = document.getElementById('advancePaymentSubmitBtn');
    const methodInput = document.getElementById('advancePaymentFormMethod');
    
    // Reset form
    form.reset();
    methodInput.value = 'PUT';
    title.textContent = 'Edit Advance Payment';
    submitBtn.textContent = 'Update Advance Payment';
    document.getElementById('advancePaymentId').value = id;
    
    // Clear errors
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    // Fetch data
    Promise.all([
        fetch(`{{ url('admin/advance-payments') }}/${id}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).then(r => r.json()),
        fetch('{{ route("admin.advance-payments.create") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).then(r => r.json())
    ])
    .then(([paymentData, formData]) => {
        const payment = paymentData.advancePayment;
        
        // Populate payment types
        const paymentTypeSelect = document.getElementById('payment_type');
        paymentTypeSelect.innerHTML = '<option value="">Select Payment Type</option>';
        formData.paymentTypes.forEach(type => {
            const typeCode = type.code || type.name.toLowerCase().replace(/\s+/g, '_');
            const selected = payment.payment_type === typeCode ? 'selected' : '';
            paymentTypeSelect.innerHTML += `<option value="${typeCode}" ${selected}>${type.name}</option>`;
        });
        
        // Populate projects
        const projectSelect = document.getElementById('project_id');
        projectSelect.innerHTML = '<option value="">None</option>';
        formData.projects.forEach(project => {
            const selected = payment.project_id == project.id ? 'selected' : '';
            projectSelect.innerHTML += `<option value="${project.id}" ${selected}>${project.name}</option>`;
        });
        
        // Populate suppliers
        const supplierSelect = document.getElementById('supplier_id');
        supplierSelect.innerHTML = '<option value="">Select Supplier</option>';
        formData.suppliers.forEach(supplier => {
            const selected = payment.supplier_id == supplier.id ? 'selected' : '';
            supplierSelect.innerHTML += `<option value="${supplier.id}" ${selected}>${supplier.name}</option>`;
        });
        
        // Populate bank accounts
        const bankAccountSelect = document.getElementById('bank_account_id');
        bankAccountSelect.innerHTML = '<option value="">None</option>';
        formData.bankAccounts.forEach(account => {
            const selected = payment.bank_account_id == account.id ? 'selected' : '';
            bankAccountSelect.innerHTML += `<option value="${account.id}" ${selected}>${account.account_name} (${account.account_type})</option>`;
        });
        
        // Fill form fields
        document.getElementById('amount').value = payment.amount;
        document.getElementById('payment_date').value = payment.payment_date.split('T')[0];
        document.getElementById('payment_method').value = payment.payment_method || '';
        document.getElementById('transaction_reference').value = payment.transaction_reference || '';
        document.getElementById('notes').value = payment.notes || '';
        
        modal.classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error loading payment data', 'error');
    });
}

function closeAdvancePaymentModal() {
    document.getElementById('advancePaymentModal').classList.add('hidden');
    document.getElementById('advancePaymentForm').reset();
}

function openViewAdvancePaymentModal(id) {
    const modal = document.getElementById('viewAdvancePaymentModal');
    const content = document.getElementById('viewAdvancePaymentContent');
    
    content.innerHTML = '<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>';
    modal.classList.remove('hidden');
    
    fetch(`{{ url('admin/advance-payments') }}/${id}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const p = data.advancePayment;
        content.innerHTML = `
            <div class="row">
                <div class="col-md-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Payment Information</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="200">Payment Date:</th>
                                    <td>${p.payment_date}</td>
                                </tr>
                                <tr>
                                    <th>Payment Type:</th>
                                    <td><span class="badge bg-info">${p.payment_type}</span></td>
                                </tr>
                                <tr>
                                    <th>Project:</th>
                                    <td>${p.project}</td>
                                </tr>
                                <tr>
                                    <th>Supplier:</th>
                                    <td><strong>${p.supplier}</strong></td>
                                </tr>
                                <tr>
                                    <th>Payment Amount:</th>
                                    <td><strong class="text-primary">Rs. ${p.amount}</strong></td>
                                </tr>
                                <tr>
                                    <th>Payment Method:</th>
                                    <td>${p.payment_method}</td>
                                </tr>
                                <tr>
                                    <th>Bank Account:</th>
                                    <td>${p.bank_account}</td>
                                </tr>
                                <tr>
                                    <th>Transaction Reference:</th>
                                    <td>${p.transaction_reference}</td>
                                </tr>
                                ${p.notes ? `<tr><th>Notes:</th><td>${p.notes}</td></tr>` : ''}
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Metadata</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-borderless table-sm">
                                <tr>
                                    <th>Created By:</th>
                                    <td>${p.creator}</td>
                                </tr>
                                <tr>
                                    <th>Created At:</th>
                                    <td>${p.created_at}</td>
                                </tr>
                                ${p.updater ? `
                                <tr>
                                    <th>Updated By:</th>
                                    <td>${p.updater}</td>
                                </tr>
                                <tr>
                                    <th>Updated At:</th>
                                    <td>${p.updated_at}</td>
                                </tr>
                                ` : ''}
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button onclick="closeViewAdvancePaymentModal(); openEditAdvancePaymentModal(${p.id})" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </button>
            </div>
        `;
    })
    .catch(error => {
        console.error('Error:', error);
        content.innerHTML = '<div class="alert alert-danger">Error loading payment details.</div>';
    });
}

function closeViewAdvancePaymentModal() {
    document.getElementById('viewAdvancePaymentModal').classList.add('hidden');
}

function showDeleteAdvancePaymentConfirmation(id, name) {
    deleteAdvancePaymentId = id;
    document.getElementById('deleteAdvancePaymentName').textContent = name;
    document.getElementById('deleteAdvancePaymentConfirmationModal').classList.remove('hidden');
}

function closeDeleteAdvancePaymentConfirmation() {
    deleteAdvancePaymentId = null;
    document.getElementById('deleteAdvancePaymentConfirmationModal').classList.add('hidden');
}

function confirmDeleteAdvancePayment() {
    if (!deleteAdvancePaymentId) return;
    
    const row = document.querySelector(`tr[data-advance-payment-id="${deleteAdvancePaymentId}"]`);
    
    fetch(`{{ url('admin/advance-payments') }}/${deleteAdvancePaymentId}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeDeleteAdvancePaymentConfirmation();
            if (row) {
                row.style.transition = 'opacity 0.3s';
                row.style.opacity = '0';
                setTimeout(() => row.remove(), 300);
            }
            showNotification(data.message || 'Advance payment deleted successfully', 'success');
        } else {
            showNotification(data.message || 'Error deleting advance payment', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error deleting advance payment', 'error');
    });
}

// Form submission
document.getElementById('advancePaymentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const submitBtn = document.getElementById('advancePaymentSubmitBtn');
    const originalText = submitBtn.textContent;
    const method = document.getElementById('advancePaymentFormMethod').value;
    const paymentId = document.getElementById('advancePaymentId').value;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    // Clear previous errors
    document.querySelectorAll('.field-error').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    
    if (method === 'PUT') {
        formData.append('_method', 'PUT');
    }
    
    const url = paymentId 
        ? `{{ url('admin/advance-payments') }}/${paymentId}`
        : '{{ route("admin.advance-payments.store") }}';
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeAdvancePaymentModal();
            if (paymentId) {
                updateAdvancePaymentRow(data.advancePayment);
            } else {
                addAdvancePaymentRow(data.advancePayment);
            }
            showNotification(data.message || 'Advance payment saved successfully', 'success');
        } else if (data.errors) {
            // Display validation errors
            Object.keys(data.errors).forEach(field => {
                const errorEl = document.querySelector(`.field-error[data-field="${field}"]`);
                if (errorEl) {
                    errorEl.textContent = data.errors[field][0];
                    errorEl.style.display = 'block';
                }
                const inputEl = document.querySelector(`[name="${field}"]`);
                if (inputEl) inputEl.classList.add('is-invalid');
            });
        } else {
            showNotification(data.message || 'Error saving advance payment', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving advance payment', 'error');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
});

function addAdvancePaymentRow(payment) {
    const tbody = document.querySelector('#advancePaymentsTableBody');
    if (!tbody) return;
    
    const row = document.createElement('tr');
    row.setAttribute('data-advance-payment-id', payment.id);
    
    const paymentType = payment.payment_type ? ucfirst(payment.payment_type.replace(/_/g, ' ')) : 'N/A';
    const paymentMethod = payment.payment_method ? ucfirst(payment.payment_method.replace(/_/g, ' ')) : 'N/A';
    
    row.innerHTML = `
        <td>${new Date(payment.payment_date).toISOString().split('T')[0]}</td>
        <td><span class="badge bg-info">${paymentType}</span></td>
        <td>N/A</td>
        <td>${payment.project ? payment.project.name : 'N/A'}</td>
        <td>${payment.supplier ? payment.supplier.name : 'N/A'}</td>
        <td><strong>Rs. ${parseFloat(payment.amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</strong></td>
        <td>${paymentMethod}</td>
        <td>
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewAdvancePaymentModal(${payment.id})" class="btn btn-sm btn-outline-primary" title="View">
                    <i class="bi bi-eye"></i>
                </button>
                <button onclick="openEditAdvancePaymentModal(${payment.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeleteAdvancePaymentConfirmation(${payment.id}, '${(payment.supplier ? payment.supplier.name : 'N/A').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
}

function updateAdvancePaymentRow(payment) {
    const row = document.querySelector(`tr[data-advance-payment-id="${payment.id}"]`);
    if (!row) return;
    
    const paymentType = payment.payment_type ? ucfirst(payment.payment_type.replace(/_/g, ' ')) : 'N/A';
    const paymentMethod = payment.payment_method ? ucfirst(payment.payment_method.replace(/_/g, ' ')) : 'N/A';
    
    row.innerHTML = `
        <td>${new Date(payment.payment_date).toISOString().split('T')[0]}</td>
        <td><span class="badge bg-info">${paymentType}</span></td>
        <td>N/A</td>
        <td>${payment.project ? payment.project.name : 'N/A'}</td>
        <td>${payment.supplier ? payment.supplier.name : 'N/A'}</td>
        <td><strong>Rs. ${parseFloat(payment.amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',')}</strong></td>
        <td>${paymentMethod}</td>
        <td>
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewAdvancePaymentModal(${payment.id})" class="btn btn-sm btn-outline-primary" title="View">
                    <i class="bi bi-eye"></i>
                </button>
                <button onclick="openEditAdvancePaymentModal(${payment.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeleteAdvancePaymentConfirmation(${payment.id}, '${(payment.supplier ? payment.supplier.name : 'N/A').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;
}

function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// Filter functions
// Debounced filter function for performance
const applyFiltersDebounced = window.debounce ? window.debounce(applyFilters, 300) : applyFilters;

let currentAdvancePaymentPage = 1;
let isLoadingAdvancePayments = false;

function applyFilters(page = 1) {
    if (isLoadingAdvancePayments) return;
    
    isLoadingAdvancePayments = true;
    currentAdvancePaymentPage = page;
    
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    // Add form values to params
    for (const [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    
    // Add page parameter
    if (page > 1) {
        params.append('page', page);
    }
    
    // Show loading state
    document.getElementById('advance-payments-loading').classList.remove('hidden');
    document.getElementById('advance-payments-table-container').style.opacity = '0.5';
    document.getElementById('advancePaymentsSummary').style.opacity = '0.5';
    
    // Fetch filtered advance payments via AJAX
    fetch(`{{ route('admin.advance-payments.index') }}?${params.toString()}`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        updateAdvancePaymentsTable(data.advancePayments);
        updateAdvancePaymentsPagination(data.pagination);
        updateAdvancePaymentsSummary(data.summary);
        updateAdvancePaymentsURL(params.toString());
        
        // Hide loading state
        document.getElementById('advance-payments-loading').classList.add('hidden');
        document.getElementById('advance-payments-table-container').style.opacity = '1';
        document.getElementById('advancePaymentsSummary').style.opacity = '1';
        isLoadingAdvancePayments = false;
    })
    .catch(error => {
        console.error('Error loading advance payments:', error);
        document.getElementById('advance-payments-loading').classList.add('hidden');
        document.getElementById('advance-payments-table-container').style.opacity = '1';
        document.getElementById('advancePaymentsSummary').style.opacity = '1';
        alert('Failed to load advance payments. Please refresh the page.');
        isLoadingAdvancePayments = false;
    });
}

function updateAdvancePaymentsTable(advancePayments) {
    const tbody = document.getElementById('advancePaymentsTableBody');
    
    if (!advancePayments || advancePayments.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center text-muted py-4">No advance payments found.</td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = advancePayments.map(payment => {
        const supplierName = payment.supplier_name || 'N/A';
        const escapedSupplierName = supplierName.replace(/'/g, "\\'");
        
        return `
            <tr data-advance-payment-id="${payment.id}">
                <td>${payment.payment_date}</td>
                <td>
                    <span class="badge bg-info">${payment.payment_type}</span>
                </td>
                <td>${payment.reference}</td>
                <td>${payment.project_name}</td>
                <td>${payment.supplier_name}</td>
                <td><strong>Rs. ${payment.amount}</strong></td>
                <td>${payment.payment_method}</td>
                <td>
                    <div class="d-flex gap-1 text-nowrap">
                        <button onclick="openViewAdvancePaymentModal(${payment.id})" class="btn btn-sm btn-outline-primary" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button onclick="openEditAdvancePaymentModal(${payment.id})" class="btn btn-sm btn-outline-warning" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button onclick="showDeleteAdvancePaymentConfirmation(${payment.id}, '${escapedSupplierName}')" class="btn btn-sm btn-outline-danger" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function updateAdvancePaymentsPagination(paginationHtml) {
    const paginationContainer = document.getElementById('advancePaymentsPagination');
    if (!paginationContainer) {
        console.error('Pagination container not found');
        return;
    }
    
    if (paginationHtml && paginationHtml.trim() !== '') {
        paginationContainer.innerHTML = paginationHtml;
        
        // Attach click handlers to pagination links
        setTimeout(() => {
            paginationContainer.querySelectorAll('a[href*="page="]').forEach(link => {
                // Remove existing listeners to avoid duplicates
                const newLink = link.cloneNode(true);
                link.parentNode.replaceChild(newLink, link);
                
                newLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const url = new URL(this.href);
                    const page = url.searchParams.get('page') || 1;
                    applyFilters(parseInt(page));
                });
            });
        }, 100);
    } else {
        paginationContainer.innerHTML = '';
    }
}

function updateAdvancePaymentsSummary(summaryData) {
    const summaryContainer = document.getElementById('advancePaymentsSummary');
    if (!summaryContainer) return;
    
    if (!summaryData) {
        summaryContainer.innerHTML = '';
        return;
    }
    
    summaryContainer.innerHTML = `
        <div class="card mb-4 shadow-sm">
            <div class="card-body py-3">
                <div class="row g-3 mb-0">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <div class="flex-shrink-0">
                                <i class="bi bi-cash-stack text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block mb-1">Total Advance Payments</small>
                                <h5 class="mb-0">Rs. ${summaryData.totalAmount}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function updateAdvancePaymentsURL(params) {
    const newURL = window.location.pathname + (params ? '?' + params : '');
    window.history.pushState({path: newURL}, '', newURL);
}

function resetFilters() {
    document.getElementById('filter_project_id').value = '';
    document.getElementById('filter_payment_type').value = '';
    document.getElementById('filter_supplier_id').value = '';
    document.getElementById('filter_start_date').value = '';
    document.getElementById('filter_end_date').value = '';
    applyFilters();
}

// Load pagination handlers on page load
document.addEventListener('DOMContentLoaded', function() {
    // Attach click handlers to existing pagination links
    const paginationContainer = document.getElementById('advancePaymentsPagination');
    if (paginationContainer) {
        paginationContainer.querySelectorAll('a[href*="page="]').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const url = new URL(this.href);
                const page = url.searchParams.get('page') || 1;
                applyFilters(parseInt(page));
            });
        });
    }
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(e) {
        if (e.state && e.state.path) {
            const url = new URL(window.location.href);
            const page = url.searchParams.get('page') || 1;
            applyFilters(parseInt(page));
        }
    });
});


// Close modals on escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('advancePaymentModal').classList.contains('hidden')) {
            closeAdvancePaymentModal();
        }
        if (!document.getElementById('viewAdvancePaymentModal').classList.contains('hidden')) {
            closeViewAdvancePaymentModal();
        }
        if (!document.getElementById('deleteAdvancePaymentConfirmationModal').classList.contains('hidden')) {
            closeDeleteAdvancePaymentConfirmation();
        }
    }
});

document.getElementById('deleteAdvancePaymentConfirmationModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteAdvancePaymentConfirmation();
});
</script>
@endsection
