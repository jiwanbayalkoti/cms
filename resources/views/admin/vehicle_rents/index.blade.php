@extends('admin.layout')

@section('title', 'Vehicle Rent Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Vehicle Rent Management</h1>
        <p class="text-muted mb-0">Manage vehicle rental records</p>
    </div>
    <div class="d-flex gap-2">
        @if($vehicleRents->count() > 0)
            <a href="{{ route('admin.vehicle-rents.export', request()->all()) }}" class="btn btn-success">
                <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
            </a>
        @endif
        <button onclick="openCreateVehicleRentModal()" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> <span class="vehicle-rent-btn-text">Add Vehicle Rent</span>
        </button>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filters</h5>
    </div>
    <div class="card-body">
        <form id="filterForm" class="row g-2 align-items-end">
            <div class="col-md-2">
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
            <div class="col-md-2">
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
            <div class="col-md-2">
                <label class="form-label small mb-1">Vehicle Type</label>
                <select name="vehicle_type" id="filter_vehicle_type" class="form-select form-select-sm" onchange="applyFiltersDebounced()">
                    <option value="" {{ !request('vehicle_type') ? 'selected' : '' }}>All Types</option>
                    @foreach($vehicleTypes as $key => $label)
                        <option value="{{ $key }}" {{ request('vehicle_type') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Payment Status</label>
                <select name="payment_status" id="filter_payment_status" class="form-select form-select-sm" onchange="applyFiltersDebounced()">
                    <option value="" {{ !request('payment_status') ? 'selected' : '' }}>All Status</option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Rate Type</label>
                <select name="rate_type" id="filter_rate_type" class="form-select form-select-sm" onchange="applyFiltersDebounced()">
                    <option value="" {{ !request('rate_type') ? 'selected' : '' }}>All Types</option>
                    <option value="fixed" {{ request('rate_type') == 'fixed' ? 'selected' : '' }}>Fixed Rate</option>
                    <option value="per_km" {{ request('rate_type') == 'per_km' ? 'selected' : '' }}>Per Kilometer</option>
                    <option value="per_hour" {{ request('rate_type') == 'per_hour' ? 'selected' : '' }}>Per Hour</option>
                    <option value="daywise" {{ request('rate_type') == 'daywise' ? 'selected' : '' }}>Daywise</option>
                    <option value="per_quintal" {{ request('rate_type') == 'per_quintal' ? 'selected' : '' }}>Per Quintal</option>
                    <option value="not_fixed" {{ request('rate_type') == 'not_fixed' ? 'selected' : '' }}>Not Fixed</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Start Date</label>
                <input type="date" name="start_date" id="filter_start_date" class="form-control form-control-sm" value="{{ request('start_date') ?: '' }}" onchange="applyFiltersDebounced()">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">End Date</label>
                <input type="date" name="end_date" id="filter_end_date" class="form-control form-control-sm" value="{{ request('end_date') ?: '' }}" onchange="applyFiltersDebounced()">
            </div>
            <div class="col-md-12"></div>
            <div class="col-md-12 mt-2">
                <button type="button" onclick="resetFilters()" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset Filters
                </button>
                <small class="text-muted ms-2">Filters apply automatically when changed</small>
            </div>
        </form>
    </div>
</div>

<div id="vehicleRentsSummary">
    @if($vehicleRents->count() > 0)
    <div class="card mb-4 shadow-sm">
        <div class="card-body py-3">
            <div class="row g-3 mb-0">
                <div class="col-md-3">
                    <div class="d-flex align-items-center p-3 bg-light rounded">
                        <div class="flex-shrink-0">
                            <i class="bi bi-cash-stack text-primary fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="text-muted d-block mb-1">Total Amount</small>
                            <h5 class="mb-0 text-primary fw-bold">{{ number_format($totalAmount, 2) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center p-3 bg-light rounded">
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle-fill text-success fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="text-muted d-block mb-1">Total Paid</small>
                            <h5 class="mb-0 text-success fw-bold">{{ number_format($totalPaid, 2) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center p-3 bg-light rounded">
                        <div class="flex-shrink-0">
                            <i class="bi bi-{{ $totalBalance > 0 ? 'exclamation-triangle-fill text-danger' : 'check-circle-fill text-success' }} fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="text-muted d-block mb-1">Remaining Balance</small>
                            <h5 class="mb-0 text-{{ $totalBalance > 0 ? 'danger' : 'success' }} fw-bold">{{ number_format($totalBalance, 2) }}</h5>
                            @if($totalBalance > 0)
                                <small class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Outstanding</small>
                            @else
                                <small class="text-success"><i class="bi bi-check-circle me-1"></i>All Paid</small>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="d-flex align-items-center p-3 bg-light rounded">
                        <div class="flex-shrink-0">
                            <i class="bi bi-wallet-fill text-info fs-4"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <small class="text-muted d-block mb-1">Advance Payments</small>
                            <h5 class="mb-0 text-info fw-bold">{{ number_format($totalAdvancePayments ?? 0, 2) }}</h5>
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Total Advances</small>
                        </div>
                    </div>
                </div>
            </div>
            @if(isset($netBalance))
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-{{ $netBalance > 0 ? 'warning' : ($netBalance < 0 ? 'success' : 'info') }} mb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong><i class="bi bi-calculator me-2"></i>Net Balance (After Advance Payments):</strong>
                                <span class="ms-2 fs-5 fw-bold">{{ number_format($netBalance, 2) }}</span>
                            </div>
                            <div>
                                @if($netBalance > 0)
                                    <span class="badge bg-warning text-dark"><i class="bi bi-arrow-up-circle me-1"></i>Outstanding Amount</span>
                                @elseif($netBalance < 0)
                                    <span class="badge bg-success"><i class="bi bi-arrow-down-circle me-1"></i>Overpaid/Advance Credit</span>
                                @else
                                    <span class="badge bg-info"><i class="bi bi-check-circle me-1"></i>Balanced</span>
                                @endif
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block">Calculation: Remaining Balance ({{ number_format($totalBalance, 2) }}) - Advance Payments ({{ number_format($totalAdvancePayments ?? 0, 2) }}) = Net Balance</small>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>

<div id="vehicle-rents-loading" class="hidden card mb-4">
    <div class="card-body text-center py-5">
        <svg class="animate-spin h-8 w-8 text-indigo-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-2 text-gray-600">Loading vehicle rents...</p>
    </div>
</div>

<div id="vehicle-rents-table-container" class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Vehicle Type</th>
                        <th>Vehicle #</th>
                        <th>Route</th>
                        <th>Rate Type</th>
                        <th>Project</th>
                        <th>Supplier</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Balance</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="vehicleRentsTableBody">
                    @forelse($vehicleRents as $rent)
                        <tr data-rent-id="{{ $rent->id }}">
                            <td>{{ $rent->rent_date->format('Y-m-d') }}</td>
                            <td>
                                <span class="badge bg-info">{{ $vehicleTypes[$rent->vehicle_type] ?? $rent->vehicle_type }}</span>
                            </td>
                            <td>{{ $rent->vehicle_number ?? '—' }}</td>
                            <td>
                                <small>
                                    <strong>From:</strong> {{ $rent->start_location }}<br>
                                    <strong>To:</strong> {{ $rent->destination_location }}
                                </small>
                            </td>
                            <td>
                                @php
                                    $rateTypeLabels = [
                                        'fixed' => 'Fixed Rate',
                                        'per_km' => 'Per KM',
                                        'per_hour' => 'Per Hour',
                                        'daywise' => 'Daywise',
                                        'per_quintal' => 'Per Quintal',
                                        'not_fixed' => 'Not Fixed',
                                    ];
                                    $rateTypeLabel = $rateTypeLabels[$rent->rate_type] ?? ucfirst(str_replace('_', ' ', $rent->rate_type));
                                @endphp
                                <span class="badge bg-secondary">{{ $rateTypeLabel }}</span>
                            </td>
                            <td>{{ $rent->project->name ?? '—' }}</td>
                            <td>{{ $rent->supplier->name ?? '—' }}</td>
                            <td class="text-end">
                                @if($rent->is_ongoing)
                                    <strong>{{ number_format($rent->calculated_total_amount, 2) }}</strong>
                                    <br><small class="text-warning"><i class="bi bi-clock"></i> Ongoing</small>
                                @else
                                    <strong>{{ number_format($rent->total_amount, 2) }}</strong>
                                @endif
                            </td>
                            <td class="text-end">{{ number_format($rent->paid_amount, 2) }}</td>
                            <td class="text-end">
                                @php
                                    $balanceAmount = $rent->is_ongoing ? $rent->calculated_balance_amount : $rent->balance_amount;
                                @endphp
                                <strong class="{{ $balanceAmount > 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($balanceAmount, 2) }}
                                </strong>
                            </td>
                            <td>
                                @php
                                    $paymentStatus = $rent->is_ongoing ? $rent->calculated_payment_status : $rent->payment_status;
                                @endphp
                                <span class="badge bg-{{ $paymentStatus === 'paid' ? 'success' : ($paymentStatus === 'partial' ? 'warning' : 'danger') }}">
                                    {{ ucfirst($paymentStatus) }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1 text-nowrap">
                                    <button onclick="openViewVehicleRentModal({{ $rent->id }})" class="btn btn-outline-primary btn-sm" title="View">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button onclick="openEditVehicleRentModal({{ $rent->id }})" class="btn btn-outline-warning btn-sm" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button onclick="showDeleteVehicleRentConfirmation({{ $rent->id }}, '{{ addslashes($rent->vehicle_number ?? 'Vehicle') }}', '{{ $rent->rent_date->format('Y-m-d') }}')" class="btn btn-outline-danger btn-sm" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-4">
                                <p class="text-muted mb-0">No vehicle rent records found.</p>
                                <a href="{{ route('admin.vehicle-rents.create') }}" class="btn btn-primary btn-sm mt-2">Add First Record</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="vehicleRentsPagination" class="mt-4">
    <x-pagination :paginator="$vehicleRents" :show-info="true" />
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteVehicleRentConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Vehicle Rent</h3>
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete vehicle rent record for <span class="font-semibold text-gray-900" id="delete-rent-vehicle"></span> (<span id="delete-rent-date"></span>)? This action cannot be undone.
            </p>
            <div class="flex space-x-3">
                <button onclick="closeDeleteVehicleRentConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeleteVehicleRent()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    @media (max-width: 768px) {
        .vehicle-rent-btn-text {
            display: none;
        }
    }
</style>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentRentId = null;
let deleteRentId = null;

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

// Modal management functions
function openCreateVehicleRentModal() {
    currentRentId = null;
    fetch('{{ route("admin.vehicle-rents.create") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        buildVehicleRentForm(data, null);
        document.getElementById('vehicleRentModal').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error loading form', 'error');
    });
}

function openEditVehicleRentModal(rentId) {
    currentRentId = rentId;
    Promise.all([
        fetch(`/admin/vehicle-rents/${rentId}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).then(r => r.json()),
        fetch('{{ route("admin.vehicle-rents.create") }}', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        }).then(r => r.json())
    ])
    .then(([rentData, formData]) => {
        buildVehicleRentForm(formData, rentData.rent);
        document.getElementById('vehicleRentModal').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error loading rent data', 'error');
    });
}

function openViewVehicleRentModal(rentId) {
    fetch(`/admin/vehicle-rents/${rentId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        buildVehicleRentView(data.rent);
        document.getElementById('viewVehicleRentModal').classList.remove('hidden');
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Error loading rent details', 'error');
    });
}

function closeVehicleRentModal() {
    document.getElementById('vehicleRentModal').classList.add('hidden');
    document.getElementById('vehicleRentFormContainer').innerHTML = '';
    currentRentId = null;
}

function closeViewVehicleRentModal() {
    document.getElementById('viewVehicleRentModal').classList.add('hidden');
    document.getElementById('viewVehicleRentContent').innerHTML = '';
}

function showDeleteVehicleRentConfirmation(rentId, vehicleNumber, rentDate) {
    deleteRentId = rentId;
    document.getElementById('delete-rent-vehicle').textContent = vehicleNumber;
    document.getElementById('delete-rent-date').textContent = rentDate;
    document.getElementById('deleteVehicleRentConfirmationModal').classList.remove('hidden');
}

function closeDeleteVehicleRentConfirmation() {
    document.getElementById('deleteVehicleRentConfirmationModal').classList.add('hidden');
    deleteRentId = null;
}

function confirmDeleteVehicleRent() {
    if (!deleteRentId) return;
    
    const deleteBtn = event.target;
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/vehicle-rents/${deleteRentId}`, {
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
            closeDeleteVehicleRentConfirmation();
            showNotification(data.message, 'success');
            deleteRentRow(deleteRentId);
        } else {
            showNotification(data.message || 'Failed to delete vehicle rent', 'error');
            deleteBtn.disabled = false;
            deleteBtn.textContent = 'Delete';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('An error occurred while deleting', 'error');
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Delete';
    });
}

// Form building - This will be a very large function due to complexity
function buildVehicleRentForm(data, rent) {
    const isEdit = !!rent;
    const container = document.getElementById('vehicleRentFormContainer');
    
    // Build vehicle types options
    const vehicleTypeOptions = Object.entries(data.vehicleTypes).map(([key, label]) => 
        `<option value="${key}" ${rent && rent.vehicle_type === key ? 'selected' : ''}>${label}</option>`
    ).join('');
    
    // Build projects options
    const projectOptions = data.projects.map(p => 
        `<option value="${p.id}" ${rent && rent.project_id == p.id ? 'selected' : ''}>${p.name}</option>`
    ).join('');
    
    // Build suppliers options
    const supplierOptions = data.suppliers.map(s => 
        `<option value="${s.id}" ${rent && rent.supplier_id == s.id ? 'selected' : ''}>${s.name}</option>`
    ).join('');
    
    // Build bank accounts options
    const bankAccountOptions = data.bankAccounts.map(ba => 
        `<option value="${ba.id}" ${rent && rent.bank_account_id == ba.id ? 'selected' : ''}>${ba.account_name} - ${ba.bank_name}</option>`
    ).join('');
    
    container.innerHTML = `
        <form id="vehicleRentForm" onsubmit="submitVehicleRentForm(event)">
            <div class="row mb-4">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Rent Date <span class="text-danger">*</span></label>
                    <input type="date" name="rent_date" id="rent_date" class="form-control" value="${rent ? rent.rent_date : '{{ date("Y-m-d") }}'}" required>
                    <div class="field-error text-danger small mt-1" data-field="rent_date" style="display: none;"></div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Project</label>
                    <select name="project_id" id="project_id" class="form-select">
                        <option value="">None</option>
                        ${projectOptions}
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" id="supplier_id" class="form-select">
                        <option value="">None</option>
                        ${supplierOptions}
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>
                    <select name="vehicle_type" id="vehicle_type" class="form-select" onchange="handleVehicleTypeChange()" required>
                        <option value="">Select Vehicle Type</option>
                        ${vehicleTypeOptions}
                    </select>
                    <div class="field-error text-danger small mt-1" data-field="vehicle_type" style="display: none;"></div>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Vehicle Number</label>
                    <input type="text" name="vehicle_number" id="vehicle_number" class="form-control" value="${rent ? rent.vehicle_number || '' : ''}" placeholder="Registration number">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Driver Name</label>
                    <input type="text" name="driver_name" id="driver_name" class="form-control" value="${rent ? rent.driver_name || '' : ''}" placeholder="Driver name">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Driver Contact</label>
                    <input type="text" name="driver_contact" id="driver_contact" class="form-control" value="${rent ? rent.driver_contact || '' : ''}" placeholder="Phone number">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Purpose</label>
                    <input type="text" name="purpose" id="purpose" class="form-control" value="${rent ? rent.purpose || '' : ''}" placeholder="Purpose of trip">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Start Location <span class="text-danger">*</span></label>
                    <input type="text" name="start_location" id="start_location" class="form-control" value="${rent ? rent.start_location : ''}" placeholder="e.g., Kathmandu" required>
                    <div class="field-error text-danger small mt-1" data-field="start_location" style="display: none;"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Destination Location <span class="text-danger">*</span></label>
                    <input type="text" name="destination_location" id="destination_location" class="form-control" value="${rent ? rent.destination_location : ''}" placeholder="e.g., Pokhara" required>
                    <div class="field-error text-danger small mt-1" data-field="destination_location" style="display: none;"></div>
                </div>
            </div>
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Rate & Payment Information</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Rate Type <span class="text-danger">*</span></label>
                            <select name="rate_type" id="rate_type" class="form-select" onchange="toggleRateFields()" required>
                                <option value="fixed" ${rent && rent.rate_type === 'fixed' ? 'selected' : (!rent ? 'selected' : '')}>Fixed Rate</option>
                                <option value="per_km" ${rent && rent.rate_type === 'per_km' ? 'selected' : ''}>Per Kilometer</option>
                                <option value="per_hour" ${rent && rent.rate_type === 'per_hour' ? 'selected' : ''}>Per Hour</option>
                                <option value="daywise" ${rent && rent.rate_type === 'daywise' ? 'selected' : ''}>Daywise</option>
                                <option value="per_quintal" ${rent && rent.rate_type === 'per_quintal' ? 'selected' : ''}>Per Quintal</option>
                                <option value="not_fixed" ${rent && rent.rate_type === 'not_fixed' ? 'selected' : ''}>Not Fixed</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3 mb-3" id="distance_field" style="display: none;">
                            <label class="form-label">Distance (km)</label>
                            <input type="number" name="distance_km" id="distance_km" step="0.01" min="0" class="form-control" value="${rent ? rent.distance_km || '' : ''}" placeholder="0.00" oninput="calculatePerKm()">
                        </div>
                        <div class="col-md-3 mb-3" id="rate_per_km_field" style="display: none;">
                            <label class="form-label">Rate per km</label>
                            <input type="number" name="rate_per_km" id="rate_per_km" step="0.01" min="0" class="form-control" value="${rent ? rent.rate_per_km || '' : ''}" placeholder="0.00" oninput="calculatePerKm()">
                        </div>
                        <div class="col-md-3 mb-3" id="hours_field" style="display: none;">
                            <label class="form-label">Hours</label>
                            <input type="number" name="hours" id="hours" min="0" max="23" class="form-control" value="${rent ? rent.hours || '' : ''}" placeholder="0" oninput="calculatePerHour()">
                        </div>
                        <div class="col-md-3 mb-3" id="minutes_field" style="display: none;">
                            <label class="form-label">Minutes</label>
                            <input type="number" name="minutes" id="minutes" min="0" max="59" class="form-control" value="${rent ? rent.minutes || '' : ''}" placeholder="0" oninput="calculatePerHour()">
                        </div>
                        <div class="col-md-3 mb-3" id="rate_per_hour_field" style="display: none;">
                            <label class="form-label">Rate per hour</label>
                            <input type="number" name="rate_per_hour" id="rate_per_hour" step="0.01" min="0" class="form-control" value="${rent ? rent.rate_per_hour || '' : ''}" placeholder="0.00" oninput="calculatePerHour()">
                        </div>
                        <div class="col-md-3 mb-3" id="rent_start_date_field" style="display: none;">
                            <label class="form-label">Rent Start Date <span class="text-danger">*</span></label>
                            <input type="date" name="rent_start_date" id="rent_start_date" class="form-control" value="${rent ? rent.rent_start_date || '' : ''}" onchange="calculateDaywise()">
                        </div>
                        <div class="col-md-3 mb-3" id="rent_end_date_field" style="display: none;">
                            <label class="form-label">Rent End Date <small class="text-muted">(Leave empty if ongoing)</small></label>
                            <input type="date" name="rent_end_date" id="rent_end_date" class="form-control" value="${rent ? rent.rent_end_date || '' : ''}" onchange="calculateDaywise()">
                        </div>
                        <div class="col-md-3 mb-3" id="number_of_days_field" style="display: none;">
                            <label class="form-label">Number of Days <small class="text-muted">(Manual entry)</small></label>
                            <input type="number" name="number_of_days" id="number_of_days" min="1" class="form-control" value="${rent ? rent.number_of_days || '' : ''}" placeholder="1" oninput="calculateDaywise()">
                        </div>
                        <div class="col-md-3 mb-3" id="rate_per_day_field" style="display: none;">
                            <label class="form-label">Rate per day</label>
                            <input type="number" name="rate_per_day" id="rate_per_day" step="0.01" min="0" class="form-control" value="${rent ? rent.rate_per_day || '' : ''}" placeholder="0.00" oninput="calculateDaywise()">
                        </div>
                        <div class="col-md-3 mb-3" id="quantity_quintal_field" style="display: none;">
                            <label class="form-label">Quantity (Quintal)</label>
                            <input type="number" name="quantity_quintal" id="quantity_quintal" step="0.01" min="0" class="form-control" value="${rent ? rent.quantity_quintal || '' : ''}" placeholder="0.00" oninput="calculatePerQuintal()">
                        </div>
                        <div class="col-md-3 mb-3" id="rate_per_quintal_field" style="display: none;">
                            <label class="form-label">Rate per quintal</label>
                            <input type="number" name="rate_per_quintal" id="rate_per_quintal" step="0.01" min="0" class="form-control" value="${rent ? rent.rate_per_quintal || '' : ''}" placeholder="0.00" oninput="calculatePerQuintal()">
                        </div>
                        <div class="col-md-3 mb-3" id="fixed_rate_field" style="display: ${rent && rent.rate_type === 'fixed' ? 'block' : (!rent ? 'block' : 'none')};">
                            <label class="form-label">Fixed Rate <span class="text-danger">*</span></label>
                            <input type="number" name="fixed_rate" id="fixed_rate" step="0.01" min="0" class="form-control" value="${rent ? rent.fixed_rate || '' : ''}" placeholder="0.00" oninput="updateTotal()">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Total Amount <span class="text-danger" id="total_amount_required">*</span></label>
                            <input type="number" name="total_amount" id="total_amount" step="0.01" min="0" class="form-control" value="${rent ? rent.total_amount || '' : ''}" oninput="updateBalance()">
                            <div class="field-error text-danger small mt-1" data-field="total_amount" style="display: none;"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Paid Amount</label>
                            <input type="number" name="paid_amount" id="paid_amount" step="0.01" min="0" class="form-control" value="${rent ? rent.paid_amount || 0 : 0}" placeholder="0.00" oninput="updateBalance()">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Balance Amount</label>
                            <input type="text" id="balance_amount" class="form-control" readonly value="${rent ? (rent.total_amount - rent.paid_amount).toFixed(2) : '0.00'}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Bank Account</label>
                            <select name="bank_account_id" id="bank_account_id" class="form-select">
                                <option value="">None</option>
                                ${bankAccountOptions}
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Date</label>
                            <input type="date" name="payment_date" id="payment_date" class="form-control" value="${rent ? rent.payment_date || '' : ''}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Notes</label>
                <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="Additional notes">${rent ? rent.notes || '' : ''}</textarea>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" onclick="closeVehicleRentModal()" class="btn btn-secondary me-2">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitVehicleRentBtn">${isEdit ? 'Update' : 'Create'} Vehicle Rent</button>
            </div>
        </form>
    `;
    
    // Initialize calculations after form is built
    setTimeout(() => {
        if (rent) {
            toggleRateFields();
            updateTotal();
        } else {
            toggleRateFields();
            updateTotalAmountField();
            updateBalance();
        }
    }, 100);
}

// Include all calculation functions from create.blade.php
function handleVehicleTypeChange() {
    const vehicleType = document.getElementById('vehicle_type').value;
    const rateTypeSelect = document.getElementById('rate_type');
    const currentRateType = rateTypeSelect.value;
    
    if (vehicleType === 'excavator' || vehicleType === 'jcv') {
        if (currentRateType === 'fixed' || currentRateType === '' || !currentRentId) {
            rateTypeSelect.value = 'per_hour';
        }
    }
    toggleRateFields();
}

function toggleRateFields() {
    const rateType = document.getElementById('rate_type')?.value;
    if (!rateType) return;
    
    const fields = ['distance_field', 'rate_per_km_field', 'hours_field', 'minutes_field', 'rate_per_hour_field', 
                    'rent_start_date_field', 'rent_end_date_field', 'number_of_days_field', 'rate_per_day_field',
                    'quantity_quintal_field', 'rate_per_quintal_field', 'fixed_rate_field'];
    
    fields.forEach(field => {
        const el = document.getElementById(field);
        if (el) el.style.display = 'none';
    });
    
    if (rateType === 'per_km') {
        document.getElementById('distance_field').style.display = 'block';
        document.getElementById('rate_per_km_field').style.display = 'block';
    } else if (rateType === 'per_hour') {
        document.getElementById('hours_field').style.display = 'block';
        document.getElementById('minutes_field').style.display = 'block';
        document.getElementById('rate_per_hour_field').style.display = 'block';
    } else if (rateType === 'daywise') {
        document.getElementById('rent_start_date_field').style.display = 'block';
        document.getElementById('rent_end_date_field').style.display = 'block';
        document.getElementById('number_of_days_field').style.display = 'block';
        document.getElementById('rate_per_day_field').style.display = 'block';
    } else if (rateType === 'per_quintal') {
        document.getElementById('quantity_quintal_field').style.display = 'block';
        document.getElementById('rate_per_quintal_field').style.display = 'block';
    } else if (rateType === 'fixed') {
        document.getElementById('fixed_rate_field').style.display = 'block';
    }
    
    updateTotalAmountField();
    updateTotal();
}

function updateTotalAmountField() {
    const rateType = document.getElementById('rate_type')?.value;
    const totalAmountField = document.getElementById('total_amount');
    const requiredIndicator = document.getElementById('total_amount_required');
    
    if (!rateType || !totalAmountField) return;
    
    if (rateType === 'not_fixed') {
        totalAmountField.removeAttribute('readonly');
        totalAmountField.removeAttribute('required');
        totalAmountField.placeholder = 'Enter total amount (optional)';
        if (requiredIndicator) requiredIndicator.style.display = 'none';
    } else {
        totalAmountField.setAttribute('readonly', 'readonly');
        totalAmountField.setAttribute('required', 'required');
        totalAmountField.placeholder = '0.00';
        if (requiredIndicator) requiredIndicator.style.display = 'inline';
    }
}

function calculatePerKm() {
    const rateType = document.getElementById('rate_type')?.value;
    if (rateType === 'per_km') {
        const distance = parseFloat(document.getElementById('distance_km')?.value || 0);
        const ratePerKm = parseFloat(document.getElementById('rate_per_km')?.value || 0);
        const total = distance * ratePerKm;
        const totalField = document.getElementById('total_amount');
        if (totalField) totalField.value = total.toFixed(2);
        updateBalance();
    }
}

function calculatePerHour() {
    const rateType = document.getElementById('rate_type')?.value;
    if (rateType === 'per_hour') {
        const hours = parseInt(document.getElementById('hours')?.value || 0);
        const minutes = parseInt(document.getElementById('minutes')?.value || 0);
        const ratePerHour = parseFloat(document.getElementById('rate_per_hour')?.value || 0);
        const totalHours = hours + (minutes / 60);
        const total = totalHours * ratePerHour;
        const totalField = document.getElementById('total_amount');
        if (totalField) totalField.value = total.toFixed(2);
        updateBalance();
    }
}

function calculateDaywise() {
    const rateType = document.getElementById('rate_type')?.value;
    if (rateType === 'daywise') {
        let numberOfDays = 0;
        const ratePerDay = parseFloat(document.getElementById('rate_per_day')?.value || 0);
        const startDate = document.getElementById('rent_start_date')?.value;
        const endDate = document.getElementById('rent_end_date')?.value;
        
        if (startDate) {
            const start = new Date(startDate);
            const end = endDate ? new Date(endDate) : new Date();
            const diffTime = Math.abs(end - start);
            numberOfDays = Math.max(1, Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1);
        } else {
            numberOfDays = parseInt(document.getElementById('number_of_days')?.value || 0);
        }
        
        const total = numberOfDays * ratePerDay;
        const totalField = document.getElementById('total_amount');
        if (totalField) totalField.value = total.toFixed(2);
        if (startDate && document.getElementById('number_of_days')) {
            document.getElementById('number_of_days').value = numberOfDays;
        }
        updateBalance();
    }
}

function calculatePerQuintal() {
    const rateType = document.getElementById('rate_type')?.value;
    if (rateType === 'per_quintal') {
        const quantityQuintal = parseFloat(document.getElementById('quantity_quintal')?.value || 0);
        const ratePerQuintal = parseFloat(document.getElementById('rate_per_quintal')?.value || 0);
        const total = quantityQuintal * ratePerQuintal;
        const totalField = document.getElementById('total_amount');
        if (totalField) totalField.value = total.toFixed(2);
        updateBalance();
    }
}

function updateTotal() {
    const rateType = document.getElementById('rate_type')?.value;
    if (!rateType) return;
    
    let total = 0;
    
    if (rateType === 'per_km') {
        const distance = parseFloat(document.getElementById('distance_km')?.value || 0);
        const ratePerKm = parseFloat(document.getElementById('rate_per_km')?.value || 0);
        total = distance * ratePerKm;
    } else if (rateType === 'per_hour') {
        const hours = parseInt(document.getElementById('hours')?.value || 0);
        const minutes = parseInt(document.getElementById('minutes')?.value || 0);
        const ratePerHour = parseFloat(document.getElementById('rate_per_hour')?.value || 0);
        const totalHours = hours + (minutes / 60);
        total = totalHours * ratePerHour;
    } else if (rateType === 'daywise') {
        let numberOfDays = 0;
        const ratePerDay = parseFloat(document.getElementById('rate_per_day')?.value || 0);
        const startDate = document.getElementById('rent_start_date')?.value;
        const endDate = document.getElementById('rent_end_date')?.value;
        
        if (startDate) {
            const start = new Date(startDate);
            const end = endDate ? new Date(endDate) : new Date();
            const diffTime = Math.abs(end - start);
            numberOfDays = Math.max(1, Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1);
        } else {
            numberOfDays = parseInt(document.getElementById('number_of_days')?.value || 0);
        }
        total = numberOfDays * ratePerDay;
    } else if (rateType === 'per_quintal') {
        const quantityQuintal = parseFloat(document.getElementById('quantity_quintal')?.value || 0);
        const ratePerQuintal = parseFloat(document.getElementById('rate_per_quintal')?.value || 0);
        total = quantityQuintal * ratePerQuintal;
    } else if (rateType === 'not_fixed') {
        total = parseFloat(document.getElementById('total_amount')?.value || 0);
    } else if (rateType === 'fixed') {
        total = parseFloat(document.getElementById('fixed_rate')?.value || 0);
    }
    
    const totalField = document.getElementById('total_amount');
    if (rateType !== 'not_fixed' && totalField) {
        totalField.value = total.toFixed(2);
    }
    updateBalance();
}

function updateBalance() {
    const total = parseFloat(document.getElementById('total_amount')?.value || 0);
    const paid = parseFloat(document.getElementById('paid_amount')?.value || 0);
    const balance = total - paid;
    const balanceField = document.getElementById('balance_amount');
    if (balanceField) balanceField.value = balance.toFixed(2);
}

function submitVehicleRentForm(e) {
    e.preventDefault();
    
    const form = document.getElementById('vehicleRentForm');
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitVehicleRentBtn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Processing...';
    
    const url = currentRentId 
        ? `/admin/vehicle-rents/${currentRentId}`
        : '/admin/vehicle-rents';
    
    if (currentRentId) {
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
            closeVehicleRentModal();
            
            if (currentRentId) {
                updateRentRow(data.rent);
            } else {
                addRentRow(data.rent);
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
        showNotification('An error occurred', 'error');
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
    });
}

function buildVehicleRentView(rent) {
    const container = document.getElementById('viewVehicleRentContent');
    const rateTypeLabels = {
        'fixed': 'Fixed Rate',
        'per_km': 'Per KM',
        'per_hour': 'Per Hour',
        'daywise': 'Daywise',
        'per_quintal': 'Per Quintal',
        'not_fixed': 'Not Fixed',
    };
    
    container.innerHTML = `
        <div class="row">
            <div class="col-md-6 mb-4">
                <h5 class="mb-3">Basic Information</h5>
                <dl class="row">
                    <dt class="col-sm-4">Rent Date</dt>
                    <dd class="col-sm-8">${rent.rent_date}</dd>
                    <dt class="col-sm-4">Vehicle Type</dt>
                    <dd class="col-sm-8">${rent.vehicle_type}</dd>
                    <dt class="col-sm-4">Vehicle Number</dt>
                    <dd class="col-sm-8">${rent.vehicle_number || '—'}</dd>
                    <dt class="col-sm-4">Driver Name</dt>
                    <dd class="col-sm-8">${rent.driver_name || '—'}</dd>
                    <dt class="col-sm-4">Driver Contact</dt>
                    <dd class="col-sm-8">${rent.driver_contact || '—'}</dd>
                    <dt class="col-sm-4">Purpose</dt>
                    <dd class="col-sm-8">${rent.purpose || '—'}</dd>
                </dl>
            </div>
            <div class="col-md-6 mb-4">
                <h5 class="mb-3">Location & Project</h5>
                <dl class="row">
                    <dt class="col-sm-4">Start Location</dt>
                    <dd class="col-sm-8">${rent.start_location}</dd>
                    <dt class="col-sm-4">Destination</dt>
                    <dd class="col-sm-8">${rent.destination_location}</dd>
                    <dt class="col-sm-4">Project</dt>
                    <dd class="col-sm-8">${rent.project_name || '—'}</dd>
                    <dt class="col-sm-4">Supplier</dt>
                    <dd class="col-sm-8">${rent.supplier_name || '—'}</dd>
                </dl>
            </div>
            <div class="col-md-6 mb-4">
                <h5 class="mb-3">Rate Information</h5>
                <dl class="row">
                    <dt class="col-sm-4">Rate Type</dt>
                    <dd class="col-sm-8"><span class="badge bg-secondary">${rent.rate_type_label}</span></dd>
                    ${rent.rate_type === 'per_km' ? `
                    <dt class="col-sm-4">Distance (km)</dt>
                    <dd class="col-sm-8">${rent.distance_km || '—'}</dd>
                    <dt class="col-sm-4">Rate per km</dt>
                    <dd class="col-sm-8">${rent.rate_per_km || '—'}</dd>
                    ` : ''}
                    ${rent.rate_type === 'per_hour' ? `
                    <dt class="col-sm-4">Hours</dt>
                    <dd class="col-sm-8">${rent.hours || 0}</dd>
                    <dt class="col-sm-4">Minutes</dt>
                    <dd class="col-sm-8">${rent.minutes || 0}</dd>
                    <dt class="col-sm-4">Rate per hour</dt>
                    <dd class="col-sm-8">${rent.rate_per_hour || '—'}</dd>
                    ` : ''}
                    ${rent.rate_type === 'daywise' ? `
                    <dt class="col-sm-4">Start Date</dt>
                    <dd class="col-sm-8">${rent.rent_start_date || '—'}</dd>
                    <dt class="col-sm-4">End Date</dt>
                    <dd class="col-sm-8">${rent.rent_end_date || 'Ongoing'}</dd>
                    <dt class="col-sm-4">Number of Days</dt>
                    <dd class="col-sm-8">${rent.number_of_days || '—'}</dd>
                    <dt class="col-sm-4">Rate per day</dt>
                    <dd class="col-sm-8">${rent.rate_per_day || '—'}</dd>
                    ` : ''}
                    ${rent.rate_type === 'per_quintal' ? `
                    <dt class="col-sm-4">Quantity (Quintal)</dt>
                    <dd class="col-sm-8">${rent.quantity_quintal || '—'}</dd>
                    <dt class="col-sm-4">Rate per quintal</dt>
                    <dd class="col-sm-8">${rent.rate_per_quintal || '—'}</dd>
                    ` : ''}
                    ${rent.rate_type === 'fixed' ? `
                    <dt class="col-sm-4">Fixed Rate</dt>
                    <dd class="col-sm-8">${rent.fixed_rate || '—'}</dd>
                    ` : ''}
                </dl>
            </div>
            <div class="col-md-6 mb-4">
                <h5 class="mb-3">Payment Information</h5>
                <dl class="row">
                    <dt class="col-sm-4">Total Amount</dt>
                    <dd class="col-sm-8"><strong>Rs. ${rent.total_amount}</strong></dd>
                    <dt class="col-sm-4">Paid Amount</dt>
                    <dd class="col-sm-8">Rs. ${rent.paid_amount}</dd>
                    <dt class="col-sm-4">Balance Amount</dt>
                    <dd class="col-sm-8"><strong class="${parseFloat(rent.balance_amount) > 0 ? 'text-danger' : 'text-success'}">Rs. ${rent.balance_amount}</strong></dd>
                    <dt class="col-sm-4">Payment Status</dt>
                    <dd class="col-sm-8">
                        <span class="badge bg-${rent.payment_status === 'paid' ? 'success' : (rent.payment_status === 'partial' ? 'warning' : 'danger')}">
                            ${rent.payment_status.charAt(0).toUpperCase() + rent.payment_status.slice(1)}
                        </span>
                    </dd>
                    <dt class="col-sm-4">Payment Date</dt>
                    <dd class="col-sm-8">${rent.payment_date || '—'}</dd>
                    <dt class="col-sm-4">Bank Account</dt>
                    <dd class="col-sm-8">${rent.bank_account_name || '—'}</dd>
                </dl>
            </div>
            ${rent.notes ? `
            <div class="col-12 mb-4">
                <h5 class="mb-3">Notes</h5>
                <p class="text-muted">${rent.notes}</p>
            </div>
            ` : ''}
        </div>
    `;
}

function addRentRow(rent) {
    const tbody = document.querySelector('table tbody');
    if (!tbody) return;
    
    const row = document.createElement('tr');
    row.setAttribute('data-rent-id', rent.id);
    
    const rateTypeLabels = {
        'fixed': 'Fixed Rate',
        'per_km': 'Per KM',
        'per_hour': 'Per Hour',
        'daywise': 'Daywise',
        'per_quintal': 'Per Quintal',
        'not_fixed': 'Not Fixed',
    };
    
    const statusClass = rent.payment_status === 'paid' ? 'success' : (rent.payment_status === 'partial' ? 'warning' : 'danger');
    
    row.innerHTML = `
        <td>${rent.rent_date}</td>
        <td><span class="badge bg-info">${rent.vehicle_type}</span></td>
        <td>${rent.vehicle_number}</td>
        <td>
            <small>
                <strong>From:</strong> ${rent.start_location}<br>
                <strong>To:</strong> ${rent.destination_location}
            </small>
        </td>
        <td><span class="badge bg-secondary">${rateTypeLabels[rent.rate_type] || rent.rate_type}</span></td>
        <td>${rent.project_name}</td>
        <td>${rent.supplier_name}</td>
        <td class="text-end"><strong>${rent.total_amount}</strong></td>
        <td class="text-end">${rent.paid_amount}</td>
        <td class="text-end">
            <strong class="${parseFloat(rent.balance_amount) > 0 ? 'text-danger' : 'text-success'}">${rent.balance_amount}</strong>
        </td>
        <td>
            <span class="badge bg-${statusClass}">${rent.payment_status.charAt(0).toUpperCase() + rent.payment_status.slice(1)}</span>
        </td>
        <td>
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewVehicleRentModal(${rent.id})" class="btn btn-outline-primary btn-sm" title="View">
                    <i class="bi bi-eye"></i>
                </button>
                <button onclick="openEditVehicleRentModal(${rent.id})" class="btn btn-outline-warning btn-sm" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeleteVehicleRentConfirmation(${rent.id}, '${rent.vehicle_number}', '${rent.rent_date}')" class="btn btn-outline-danger btn-sm" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
}

function updateRentRow(rent) {
    const row = document.querySelector(`tr[data-rent-id="${rent.id}"]`);
    if (!row) return;
    
    const rateTypeLabels = {
        'fixed': 'Fixed Rate',
        'per_km': 'Per KM',
        'per_hour': 'Per Hour',
        'daywise': 'Daywise',
        'per_quintal': 'Per Quintal',
        'not_fixed': 'Not Fixed',
    };
    
    const statusClass = rent.payment_status === 'paid' ? 'success' : (rent.payment_status === 'partial' ? 'warning' : 'danger');
    
    row.innerHTML = `
        <td>${rent.rent_date}</td>
        <td><span class="badge bg-info">${rent.vehicle_type}</span></td>
        <td>${rent.vehicle_number}</td>
        <td>
            <small>
                <strong>From:</strong> ${rent.start_location}<br>
                <strong>To:</strong> ${rent.destination_location}
            </small>
        </td>
        <td><span class="badge bg-secondary">${rateTypeLabels[rent.rate_type] || rent.rate_type}</span></td>
        <td>${rent.project_name}</td>
        <td>${rent.supplier_name}</td>
        <td class="text-end"><strong>${rent.total_amount}</strong></td>
        <td class="text-end">${rent.paid_amount}</td>
        <td class="text-end">
            <strong class="${parseFloat(rent.balance_amount) > 0 ? 'text-danger' : 'text-success'}">${rent.balance_amount}</strong>
        </td>
        <td>
            <span class="badge bg-${statusClass}">${rent.payment_status.charAt(0).toUpperCase() + rent.payment_status.slice(1)}</span>
        </td>
        <td>
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewVehicleRentModal(${rent.id})" class="btn btn-outline-primary btn-sm" title="View">
                    <i class="bi bi-eye"></i>
                </button>
                <button onclick="openEditVehicleRentModal(${rent.id})" class="btn btn-outline-warning btn-sm" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeleteVehicleRentConfirmation(${rent.id}, '${rent.vehicle_number}', '${rent.rent_date}')" class="btn btn-outline-danger btn-sm" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    `;
}

function deleteRentRow(rentId) {
    const row = document.querySelector(`tr[data-rent-id="${rentId}"]`);
    if (row) {
        row.style.transition = 'opacity 0.3s';
        row.style.opacity = '0';
        setTimeout(() => row.remove(), 300);
    }
}

// Modal event listeners
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('vehicleRentModal').classList.contains('hidden')) {
            closeVehicleRentModal();
        }
        if (!document.getElementById('viewVehicleRentModal').classList.contains('hidden')) {
            closeViewVehicleRentModal();
        }
        if (!document.getElementById('deleteVehicleRentConfirmationModal').classList.contains('hidden')) {
            closeDeleteVehicleRentConfirmation();
        }
    }
});

document.getElementById('deleteVehicleRentConfirmationModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeDeleteVehicleRentConfirmation();
});

// Filter functions
// Debounced filter function for performance
const applyFiltersDebounced = window.debounce ? window.debounce(applyFilters, 300) : applyFilters;

let currentVehicleRentPage = 1;
let isLoadingVehicleRents = false;

function applyFilters(page = 1) {
    if (isLoadingVehicleRents) return;
    
    isLoadingVehicleRents = true;
    currentVehicleRentPage = page;
    
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
    document.getElementById('vehicle-rents-loading').classList.remove('hidden');
    document.getElementById('vehicle-rents-table-container').style.opacity = '0.5';
    document.getElementById('vehicleRentsSummary').style.opacity = '0.5';
    
    // Fetch filtered vehicle rents via AJAX
    fetch(`{{ route('admin.vehicle-rents.index') }}?${params.toString()}`, {
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
        updateVehicleRentsTable(data.vehicleRents);
        updateVehicleRentsPagination(data.pagination);
        updateVehicleRentsSummary(data.summary);
        updateVehicleRentsURL(params.toString());
        
        // Hide loading state
        document.getElementById('vehicle-rents-loading').classList.add('hidden');
        document.getElementById('vehicle-rents-table-container').style.opacity = '1';
        document.getElementById('vehicleRentsSummary').style.opacity = '1';
        isLoadingVehicleRents = false;
    })
    .catch(error => {
        console.error('Error loading vehicle rents:', error);
        document.getElementById('vehicle-rents-loading').classList.add('hidden');
        document.getElementById('vehicle-rents-table-container').style.opacity = '1';
        document.getElementById('vehicleRentsSummary').style.opacity = '1';
        alert('Failed to load vehicle rents. Please refresh the page.');
        isLoadingVehicleRents = false;
    });
}

function updateVehicleRentsTable(vehicleRents) {
    const tbody = document.getElementById('vehicleRentsTableBody');
    
    if (!vehicleRents || vehicleRents.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="12" class="text-center py-4">
                    <p class="text-muted mb-0">No vehicle rent records found.</p>
                    <button onclick="openCreateVehicleRentModal()" class="btn btn-primary btn-sm mt-2">Add First Record</button>
                </td>
            </tr>
        `;
        return;
    }
    
    // Clear and rebuild table rows
    tbody.innerHTML = '';
    
    vehicleRents.forEach(rent => {
        const vehicleNumber = rent.vehicle_number || '—';
        const escapedVehicleNumber = vehicleNumber.replace(/'/g, "\\'");
        const isOngoing = rent.is_ongoing ? `<br><small class="text-warning"><i class="bi bi-clock"></i> Ongoing</small>` : '';
        
        const row = document.createElement('tr');
        row.setAttribute('data-rent-id', rent.id);
        row.innerHTML = `
            <td>${rent.rent_date}</td>
            <td><span class="badge bg-info">${rent.vehicle_type}</span></td>
            <td>${vehicleNumber}</td>
            <td><small>${rent.route}</small></td>
            <td><span class="badge bg-secondary">${rent.rate_type}</span></td>
            <td>${rent.project_name}</td>
            <td>${rent.supplier_name}</td>
            <td class="text-end">
                <strong>${rent.total_amount}</strong>
                ${isOngoing}
            </td>
            <td class="text-end">${rent.paid_amount}</td>
            <td class="text-end">
                <strong class="${parseFloat(rent.balance) > 0 ? 'text-danger' : 'text-success'}">${rent.balance}</strong>
            </td>
            <td>
                <span class="${rent.status_class}">
                    <i class="bi ${rent.status_icon} me-1"></i>${rent.payment_status}
                </span>
            </td>
            <td>
                <div class="d-flex gap-1 text-nowrap">
                    <button onclick="openViewVehicleRentModal(${rent.id})" class="btn btn-outline-primary btn-sm" title="View">
                        <i class="bi bi-eye"></i>
                    </button>
                    <button onclick="openEditVehicleRentModal(${rent.id})" class="btn btn-outline-warning btn-sm" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button onclick="showDeleteVehicleRentConfirmation(${rent.id}, '${escapedVehicleNumber}', '${rent.rent_date}')" class="btn btn-outline-danger btn-sm" title="Delete">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function updateVehicleRentsPagination(paginationHtml) {
    const paginationContainer = document.getElementById('vehicleRentsPagination');
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

function updateVehicleRentsSummary(summaryData) {
    const summaryContainer = document.getElementById('vehicleRentsSummary');
    if (!summaryContainer) return;
    
    if (!summaryData) {
        summaryContainer.innerHTML = '';
        return;
    }
    
    const balanceClass = parseFloat(summaryData.totalBalance) > 0 ? 'danger' : 'success';
    const netBalanceClass = parseFloat(summaryData.netBalance) > 0 ? 'warning' : (parseFloat(summaryData.netBalance) < 0 ? 'success' : 'info');
    const netBalanceBadge = parseFloat(summaryData.netBalance) > 0 
        ? '<span class="badge bg-warning text-dark"><i class="bi bi-arrow-up-circle me-1"></i>Outstanding Amount</span>'
        : (parseFloat(summaryData.netBalance) < 0 
            ? '<span class="badge bg-success"><i class="bi bi-arrow-down-circle me-1"></i>Overpaid/Advance Credit</span>'
            : '<span class="badge bg-info"><i class="bi bi-check-circle me-1"></i>Balanced</span>');
    
    summaryContainer.innerHTML = `
        <div class="card mb-4 shadow-sm">
            <div class="card-body py-3">
                <div class="row g-3 mb-0">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <div class="flex-shrink-0">
                                <i class="bi bi-cash-stack text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block mb-1">Total Amount</small>
                                <h5 class="mb-0 text-primary fw-bold">${summaryData.totalAmount}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <div class="flex-shrink-0">
                                <i class="bi bi-check-circle-fill text-success fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block mb-1">Total Paid</small>
                                <h5 class="mb-0 text-success fw-bold">${summaryData.totalPaid}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <div class="flex-shrink-0">
                                <i class="bi bi-${parseFloat(summaryData.totalBalance) > 0 ? 'exclamation-triangle-fill text-danger' : 'check-circle-fill text-success'} fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block mb-1">Remaining Balance</small>
                                <h5 class="mb-0 text-${balanceClass} fw-bold">${summaryData.totalBalance}</h5>
                                ${parseFloat(summaryData.totalBalance) > 0 
                                    ? '<small class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Outstanding</small>'
                                    : '<small class="text-success"><i class="bi bi-check-circle me-1"></i>All Paid</small>'}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex align-items-center p-3 bg-light rounded">
                            <div class="flex-shrink-0">
                                <i class="bi bi-wallet-fill text-info fs-4"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <small class="text-muted d-block mb-1">Advance Payments</small>
                                <h5 class="mb-0 text-info fw-bold">${summaryData.totalAdvancePayments}</h5>
                                <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Total Advances</small>
                            </div>
                        </div>
                    </div>
                </div>
                ${summaryData.hasNetBalance ? `
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-${netBalanceClass} mb-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong><i class="bi bi-calculator me-2"></i>Net Balance (After Advance Payments):</strong>
                                    <span class="ms-2 fs-5 fw-bold">${summaryData.netBalance}</span>
                                </div>
                                <div>
                                    ${netBalanceBadge}
                                </div>
                            </div>
                            <small class="text-muted mt-2 d-block">Calculation: Remaining Balance (${summaryData.totalBalance}) - Advance Payments (${summaryData.totalAdvancePayments}) = Net Balance</small>
                        </div>
                    </div>
                </div>
                ` : ''}
            </div>
        </div>
    `;
}

function updateVehicleRentsURL(params) {
    const newURL = window.location.pathname + (params ? '?' + params : '');
    window.history.pushState({path: newURL}, '', newURL);
}

function resetFilters() {
    document.getElementById('filter_project_id').value = '';
    document.getElementById('filter_supplier_id').value = '';
    document.getElementById('filter_vehicle_type').value = '';
    document.getElementById('filter_payment_status').value = '';
    document.getElementById('filter_rate_type').value = '';
    document.getElementById('filter_start_date').value = '';
    document.getElementById('filter_end_date').value = '';
    applyFilters();
}

// Handle pagination links with AJAX - using event delegation
function handlePaginationClick(e) {
    // Check if clicked element or its parent is a pagination link
    let paginationLink = null;
    
    // Check if clicked element is a link
    if (e.target.tagName === 'A' && e.target.href) {
        // Check if it's inside pagination and contains vehicle-rents
        if ((e.target.closest('.pagination') || e.target.closest('#vehicleRentsPagination')) && e.target.href.includes('vehicle-rents')) {
            paginationLink = e.target;
        }
    }
    
    // Check if clicked element is inside a pagination link
    if (!paginationLink) {
        const link = e.target.closest('a');
        if (link && link.href && link.href.includes('vehicle-rents')) {
            if (link.closest('.pagination') || link.closest('#vehicleRentsPagination')) {
                paginationLink = link;
            }
        }
    }
    
    if (paginationLink) {
        e.preventDefault();
        e.stopPropagation();
        
        const url = new URL(paginationLink.href);
        const params = url.searchParams;
        
        // Preserve filter values
        const projectId = document.getElementById('filter_project_id')?.value;
        const supplierId = document.getElementById('filter_supplier_id')?.value;
        const vehicleType = document.getElementById('filter_vehicle_type')?.value;
        const paymentStatus = document.getElementById('filter_payment_status')?.value;
        const rateType = document.getElementById('filter_rate_type')?.value;
        const startDate = document.getElementById('filter_start_date')?.value;
        const endDate = document.getElementById('filter_end_date')?.value;
        
        if (projectId) params.set('project_id', projectId);
        if (supplierId) params.set('supplier_id', supplierId);
        if (vehicleType) params.set('vehicle_type', vehicleType);
        if (paymentStatus) params.set('payment_status', paymentStatus);
        if (rateType) params.set('rate_type', rateType);
        if (startDate) params.set('start_date', startDate);
        if (endDate) params.set('end_date', endDate);
        
        const tbody = document.getElementById('vehicleRentsTableBody');
        const pagination = document.getElementById('vehicleRentsPagination');
        const summary = document.getElementById('vehicleRentsSummary');
        
        if (tbody) tbody.innerHTML = '<tr><td colspan="12" class="text-center py-4 text-muted">Loading...</td></tr>';
        
        fetch(`${url.pathname}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (tbody) tbody.innerHTML = data.html;
            if (pagination) {
                pagination.innerHTML = data.pagination || '';
                // Re-attach event listener to new pagination links
                attachPaginationListeners();
            }
            if (summary && data.summary) summary.innerHTML = data.summary;
            
            // Scroll to top of table
            if (tbody) {
                tbody.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (tbody) tbody.innerHTML = '<tr><td colspan="12" class="text-center py-4 text-danger">Error loading data.</td></tr>';
        });
    }
}

function attachPaginationListeners() {
    const paginationContainer = document.getElementById('vehicleRentsPagination');
    if (paginationContainer) {
        // Remove old listener if exists
        paginationContainer.removeEventListener('click', handlePaginationClick);
        // Add new listener
        paginationContainer.addEventListener('click', handlePaginationClick);
    }
}

// Attach pagination listener on page load
// Load pagination handlers on page load
document.addEventListener('DOMContentLoaded', function() {
    // Attach click handlers to existing pagination links
    const paginationContainer = document.getElementById('vehicleRentsPagination');
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
    
    // Original DOMContentLoaded code
    attachPaginationListeners();
});

// Also use document-level delegation as fallback
document.addEventListener('click', handlePaginationClick);
</script>
@endpush

<!-- Vehicle Rent Modal -->
<div id="vehicleRentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-2xl max-w-5xl w-full my-8 relative" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg z-10">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900" id="vehicleRentModalTitle">Add Vehicle Rent</h2>
                <button onclick="closeVehicleRentModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none" type="button">
                    <i class="bi bi-x-lg text-2xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(100vh-200px)]">
            <div id="vehicleRentFormContainer"></div>
        </div>
    </div>
</div>

<!-- View Vehicle Rent Modal -->
<div id="viewVehicleRentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-lg shadow-2xl max-w-4xl w-full my-8 relative" onclick="event.stopPropagation()">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 rounded-t-lg z-10">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900">Vehicle Rent Details</h2>
                <button onclick="closeViewVehicleRentModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none" type="button">
                    <i class="bi bi-x-lg text-2xl"></i>
                </button>
            </div>
        </div>
        <div class="p-6 overflow-y-auto max-h-[calc(100vh-200px)]">
            <div id="viewVehicleRentContent"></div>
            <div class="flex justify-end mt-6">
                <button onclick="closeViewVehicleRentModal()" class="px-6 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

