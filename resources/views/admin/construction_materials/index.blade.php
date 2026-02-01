@extends('admin.layout')

@section('title', 'Construction Materials')

@section('content')
<style>
    /* Mobile view: Hide text and show only icon for action buttons */
    @media (max-width: 768px) {
        .material-action-btn .btn-text {
            display: none;
        }
        .material-action-btn i {
            margin-right: 0 !important;
        }
        .material-action-btn {
            padding: 0.5rem !important;
            min-width: 40px;
            justify-content: center;
        }
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Construction Materials</h1>
        <small class="text-muted">Manage all materials received on site</small>
    </div>
    <div>
        <button onclick="openCreateMaterialModal()" class="btn btn-primary material-action-btn">
            <i class="bi bi-plus-lg"></i> <span class="btn-text">Add Material</span>
        </button>
        <a href="{{ route('admin.construction-materials.export', request()->query()) }}" class="btn btn-success ms-2 material-action-btn">
            <i class="bi bi-file-earmark-excel"></i> <span class="btn-text">Export Excel</span>
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <strong>Search &amp; Filter</strong>
    </div>
    <div class="card-body">
        <form id="filterForm" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Material Name</label>
                <select name="material_name" id="filter_material_name" class="form-select form-select-sm" onchange="applyFiltersDebounced()">
                    <option value="" {{ !request('material_name') ? 'selected' : '' }}>All materials</option>
                    @foreach($materialNames as $materialName)
                        <option value="{{ $materialName->name }}" {{ request('material_name') === $materialName->name ? 'selected' : '' }}>
                            {{ $materialName->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Supplier</label>
                <select name="supplier_id" id="filter_supplier_id" class="form-select form-select-sm" onchange="applyFiltersDebounced()">
                    <option value="" {{ !request('supplier_id') ? 'selected' : '' }}>All suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Project</label>
                <select name="project_name" id="filter_project_name" class="form-select form-select-sm" onchange="applyFiltersDebounced()">
                    <option value="" {{ !request('project_name') ? 'selected' : '' }}>All projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->name }}" {{ request('project_name') === $project->name ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Purchased / Payment By</label>
                <select name="purchased_by_id" id="filter_purchased_by_id" class="form-select form-select-sm" onchange="applyFiltersDebounced()">
                    <option value="" {{ !request('purchased_by_id') ? 'selected' : '' }}>All</option>
                    @foreach($purchasedBies as $person)
                        <option value="{{ $person->id }}" {{ request('purchased_by_id') == $person->id ? 'selected' : '' }}>{{ $person->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Payment Status</label>
                <select name="payment_status" id="filter_payment_status" class="form-select form-select-sm" onchange="applyFiltersDebounced()">
                    <option value="" {{ !request('payment_status') ? 'selected' : '' }}>All</option>
                    <option value="Paid" {{ request('payment_status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Unpaid" {{ request('payment_status') === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="Partial" {{ request('payment_status') === 'Partial' ? 'selected' : '' }}>Partial</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">From Delivery Date</label>
                <input type="date" name="from_date" id="filter_from_date" value="{{ request('from_date') ?: '' }}" class="form-control form-control-sm" onchange="applyFiltersDebounced()">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">To Delivery Date</label>
                <input type="date" name="to_date" id="filter_to_date" value="{{ request('to_date') ?: '' }}" class="form-control form-control-sm" onchange="applyFiltersDebounced()">
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

<div id="materialsSummary">
    @if($materials->count() > 0)
    <div class="card mb-4 shadow-sm">
        <div class="card-body py-3">
            @include('admin.construction_materials.partials.summary', ['totalCost' => $totalCost, 'totalAdvancePayments' => $totalAdvancePayments ?? 0, 'netBalance' => $netBalance ?? $totalCost])
        </div>
    </div>
    @endif
</div>

<div class="card">
    <div class="card-header">
        <strong>Material Records</strong>
    </div>
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Material</th>
                    <th>Project</th>
                    <th>Supplier</th>
                    <th>Qty Received</th>
                    <th>Qty Used</th>
                    <th>Qty Remaining</th>
                    <th>Total Cost</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            @include('admin.construction_materials.partials.table', [
                'materials' => $materials, 
                'totalCost' => $totalCost, 
                'totalAdvancePayments' => $totalAdvancePayments ?? 0, 
                'netBalance' => $netBalance ?? $totalCost
            ])
        </table>
    </div>
    <div id="materialsPagination">
        @include('admin.construction_materials.partials.pagination', ['materials' => $materials])
    </div>
</div>

<!-- Delete Material Confirmation Modal -->
<div id="deleteMaterialConfirmationModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl max-w-md w-full" onclick="event.stopPropagation()">
        <div class="p-6">
            <!-- Icon -->
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>
            
            <!-- Title -->
            <h3 class="text-xl font-semibold text-gray-900 text-center mb-2">Delete Material</h3>
            
            <!-- Message -->
            <p class="text-gray-600 text-center mb-6">
                Are you sure you want to delete <span class="font-semibold text-gray-900" id="delete-material-name"></span>? This action cannot be undone.
            </p>
            
            <!-- Buttons -->
            <div class="flex space-x-3">
                <button onclick="closeDeleteMaterialConfirmation()" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors font-medium">
                    Cancel
                </button>
                <button onclick="confirmDeleteMaterial()" class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Material Modal -->
<div id="viewMaterialModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[95vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900">Material Details</h3>
            <button onclick="closeViewMaterialModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6" id="view-material-content">
            <div class="flex items-center justify-center py-12">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Material Modal -->
<div id="materialModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-6xl w-full max-h-[95vh] overflow-hidden flex flex-col" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between p-6 border-b">
            <h3 class="text-xl font-semibold text-gray-900" id="material-modal-title">Add Material</h3>
            <button onclick="closeMaterialModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-6" id="material-modal-content">
            <div class="flex items-center justify-center py-12">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
let currentMaterialId = null;

// Open Create Modal
function openCreateMaterialModal() {
    currentMaterialId = null;
    const modal = document.getElementById('materialModal');
    const title = document.getElementById('material-modal-title');
    const content = document.getElementById('material-modal-content');
    
    modal.classList.remove('hidden');
    title.textContent = 'Add Material';
    content.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    
    // Load form via AJAX
    fetch('{{ route("admin.construction-materials.create") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Build form HTML
        const formHtml = buildMaterialForm(data, null);
        content.innerHTML = formHtml;
    })
    .catch(error => {
        console.error('Error loading form:', error);
        content.innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-600 mb-4">Failed to load form. Please try again.</p>
                <button onclick="closeMaterialModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    });
}

// Open Edit Modal
function openEditMaterialModal(materialId) {
    currentMaterialId = materialId;
    const modal = document.getElementById('materialModal');
    const title = document.getElementById('material-modal-title');
    const content = document.getElementById('material-modal-content');
    
    modal.classList.remove('hidden');
    title.textContent = 'Edit Material';
    content.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    
    // Load form via AJAX
    fetch(`/admin/construction-materials/${materialId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Build form HTML
        const formHtml = buildMaterialForm(data, data.material);
        content.innerHTML = formHtml;
    })
    .catch(error => {
        console.error('Error loading form:', error);
        content.innerHTML = `
            <div class="text-center py-12">
                <p class="text-red-600 mb-4">Failed to load form. Please try again.</p>
                <button onclick="closeMaterialModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                    Close
                </button>
            </div>
        `;
    });
}

// Build Material Form HTML
function buildMaterialForm(data, material) {
    const isEdit = material !== null;
    const materialData = material || {};
    
    return `
        <form id="materialForm" onsubmit="submitMaterialForm(event)" enctype="multipart/form-data">
            @csrf
            ${isEdit ? '<input type="hidden" name="_method" value="PUT">' : ''}
            
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Material Name *</label>
                    <select name="material_name" class="form-select" required>
                        <option value="">Select material name</option>
                        ${data.materialNames.map(m => `<option value="${m.name}" ${materialData.material_name === m.name ? 'selected' : ''}>${m.name}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Material Category</label>
                    <select name="material_category" class="form-select">
                        <option value="">Select category</option>
                        ${data.categories.map(c => `<option value="${c.name}" ${materialData.material_category === c.name ? 'selected' : ''}>${c.name}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unit *</label>
                    <select name="unit" class="form-select" required>
                        <option value="">Select unit</option>
                        ${data.units.map(u => `<option value="${u.name}" ${materialData.unit === u.name ? 'selected' : ''}>${u.name}</option>`).join('')}
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Quantity Received *</label>
                    <input type="number" step="0.01" name="quantity_received" class="form-control" value="${materialData.quantity_received || 0}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Rate per Unit *</label>
                    <input type="number" step="0.01" name="rate_per_unit" class="form-control" value="${materialData.rate_per_unit || 0}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantity Used</label>
                    <input type="number" step="0.01" name="quantity_used" class="form-control" value="${materialData.quantity_used || 0}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Wastage Quantity</label>
                    <input type="number" step="0.01" name="wastage_quantity" class="form-control" value="${materialData.wastage_quantity || 0}">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Supplier Name</label>
                    <select name="supplier_name" class="form-select">
                        <option value="">Select supplier</option>
                        ${data.suppliers.map(s => `<option value="${s.name}" ${materialData.supplier_name === s.name ? 'selected' : ''}>${s.name}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Supplier Contact</label>
                    <input type="text" name="supplier_contact" class="form-control" value="${materialData.supplier_contact || ''}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bill Number</label>
                    <input type="text" name="bill_number" class="form-control" value="${materialData.bill_number || ''}">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Bill Date</label>
                    <input type="date" name="bill_date" class="form-control" value="${materialData.bill_date ? new Date(materialData.bill_date).toISOString().split('T')[0] : ''}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Status *</label>
                    <select name="payment_status" class="form-select" required>
                        <option value="Paid" ${materialData.payment_status === 'Paid' ? 'selected' : ''}>Paid</option>
                        <option value="Unpaid" ${!materialData.payment_status || materialData.payment_status === 'Unpaid' ? 'selected' : ''}>Unpaid</option>
                        <option value="Partial" ${materialData.payment_status === 'Partial' ? 'selected' : ''}>Partial</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Payment Mode</label>
                    <select name="payment_mode" class="form-select">
                        <option value="">Select payment mode</option>
                        ${data.paymentModes.map(pm => `<option value="${pm.name}" ${materialData.payment_mode === pm.name ? 'selected' : ''}>${pm.name}</option>`).join('')}
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Purchased / Payment By</label>
                    <select name="purchased_by_id" class="form-select">
                        <option value="">Select person</option>
                        ${data.purchasedBies.map(pb => `<option value="${pb.id}" ${materialData.purchased_by_id == pb.id ? 'selected' : ''}>${pb.name}</option>`).join('')}
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Delivery Date</label>
                    <input type="date" name="delivery_date" class="form-control" value="${materialData.delivery_date ? new Date(materialData.delivery_date).toISOString().split('T')[0] : ''}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Delivery Site</label>
                    <input type="text" name="delivery_site" class="form-control" value="${materialData.delivery_site || ''}">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Delivered By</label>
                    <input type="text" name="delivered_by" class="form-control" value="${materialData.delivered_by || ''}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Received By</label>
                    <input type="text" name="received_by" class="form-control" value="${materialData.received_by || ''}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Project *</label>
                    <select name="project_id" class="form-select" required>
                        <option value="">Select project</option>
                        ${data.projects.map(p => `<option value="${p.id}" ${materialData.project_id == p.id ? 'selected' : ''}>${p.name}</option>`).join('')}
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Work Type</label>
                    <input type="text" name="work_type" class="form-control" value="${materialData.work_type || ''}" placeholder="Work Type">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Status *</label>
                    <select name="status" class="form-select" required>
                        <option value="Received" ${!materialData.status || materialData.status === 'Received' ? 'selected' : ''}>Received</option>
                        <option value="Pending" ${materialData.status === 'Pending' ? 'selected' : ''}>Pending</option>
                        <option value="Returned" ${materialData.status === 'Returned' ? 'selected' : ''}>Returned</option>
                        <option value="Damaged" ${materialData.status === 'Damaged' ? 'selected' : ''}>Damaged</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Approved By</label>
                    <input type="text" name="approved_by" class="form-control" value="${materialData.approved_by || ''}">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Approval Date</label>
                    <input type="date" name="approval_date" class="form-control" value="${materialData.approval_date ? new Date(materialData.approval_date).toISOString().split('T')[0] : ''}">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Usage Purpose</label>
                    <textarea name="usage_purpose" rows="3" class="form-control">${materialData.usage_purpose || ''}</textarea>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Bill Attachment (PDF / Image)</label>
                    <input type="file" name="bill_attachment" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    ${materialData.bill_attachment ? `<small class="d-block mt-1">Current: <a href="/storage/${materialData.bill_attachment}" target="_blank">View</a></small>` : ''}
                </div>
                <div class="col-md-6">
                    <label class="form-label">Delivery Photo</label>
                    <input type="file" name="delivery_photo" class="form-control" accept=".jpg,.jpeg,.png">
                    ${materialData.delivery_photo ? `<small class="d-block mt-1">Current: <a href="/storage/${materialData.delivery_photo}" target="_blank">View</a></small>` : ''}
                </div>
            </div>
            
            <div class="mt-4 d-flex justify-content-end gap-2">
                <button type="button" onclick="closeMaterialModal()" class="btn btn-secondary">Cancel</button>
                <button type="submit" class="btn btn-primary" id="materialSubmitBtn">${isEdit ? 'Update' : 'Save'} Material</button>
            </div>
        </form>
    `;
}

// Submit Material Form
function submitMaterialForm(e) {
    e.preventDefault();
    const form = e.target;
    const submitBtn = document.getElementById('materialSubmitBtn');
    const originalText = submitBtn.textContent;
    
    submitBtn.disabled = true;
    submitBtn.textContent = 'Saving...';
    
    const formData = new FormData(form);
    const url = currentMaterialId 
        ? `/admin/construction-materials/${currentMaterialId}`
        : '/admin/construction-materials';
    const method = currentMaterialId ? 'POST' : 'POST';
    
    if (currentMaterialId) {
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
            closeMaterialModal();
            
            if (currentMaterialId) {
                updateMaterialRow(data.material);
            } else {
                addMaterialRow(data.material);
            }
        } else {
            showNotification(data.message || 'An error occurred', 'error');
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

// Close Modal
function closeMaterialModal() {
    document.getElementById('materialModal').classList.add('hidden');
    currentMaterialId = null;
    setTimeout(() => {
        document.getElementById('material-modal-content').innerHTML = `
            <div class="flex items-center justify-center py-12">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        `;
    }, 300);
}

// Add Material Row
function addMaterialRow(material) {
    const tbody = document.querySelector('table tbody');
    const emptyRow = tbody.querySelector('tr td[colspan]');
    
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }
    
    const row = document.createElement('tr');
    row.setAttribute('data-material-id', material.id);
    row.innerHTML = `
        <td>${material.id}</td>
        <td>
            <div class="fw-semibold">${material.material_name}</div>
            <small class="text-muted">${material.material_category || ''}</small>
        </td>
        <td>${material.project_name}</td>
        <td>${material.supplier_name || ''}</td>
        <td>${parseFloat(material.quantity_received).toFixed(2)} ${material.unit}</td>
        <td>${parseFloat(material.quantity_used || 0).toFixed(2)} ${material.unit}</td>
        <td>${parseFloat(material.quantity_remaining || 0).toFixed(2)} ${material.unit}</td>
        <td>${parseFloat(material.total_cost).toFixed(2)}</td>
        <td><span class="badge bg-secondary">${material.status}</span></td>
        <td class="text-end">
            <div class="d-flex gap-1 justify-content-end">
                <button onclick="openViewMaterialModal(${material.id})" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye me-1"></i> View
                </button>
                <button onclick="openEditMaterialModal(${material.id})" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-pencil me-1"></i> Edit
                </button>
                <a href="/admin/construction-materials/${material.id}/clone" class="btn btn-sm btn-outline-info" onclick="return confirm('Are you sure you want to duplicate this material record?');">
                    <i class="bi bi-files me-1"></i> Duplicate
                </a>
                <button onclick="showDeleteMaterialConfirmation(${material.id}, '${material.material_name.replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash me-1"></i> Delete
                </button>
            </div>
        </td>
    `;
    
    tbody.insertBefore(row, tbody.firstChild);
}

// Update Material Row
function updateMaterialRow(material) {
    const row = document.querySelector(`tr[data-material-id="${material.id}"]`);
    if (row) {
        row.innerHTML = `
            <td>${material.id}</td>
            <td>
                <div class="fw-semibold">${material.material_name}</div>
                <small class="text-muted">${material.material_category || ''}</small>
            </td>
            <td>${material.project_name}</td>
            <td>${material.supplier_name || ''}</td>
            <td>${parseFloat(material.quantity_received).toFixed(2)} ${material.unit}</td>
            <td>${parseFloat(material.quantity_used || 0).toFixed(2)} ${material.unit}</td>
            <td>${parseFloat(material.quantity_remaining || 0).toFixed(2)} ${material.unit}</td>
            <td>${parseFloat(material.total_cost).toFixed(2)}</td>
            <td><span class="badge bg-secondary">${material.status}</span></td>
            <td class="text-end">
                <div class="d-flex gap-1 justify-content-end">
                    <button onclick="openViewMaterialModal(${material.id})" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eye me-1"></i> View
                    </button>
                    <button onclick="openEditMaterialModal(${material.id})" class="btn btn-sm btn-outline-warning">
                        <i class="bi bi-pencil me-1"></i> Edit
                    </button>
                    <a href="/admin/construction-materials/${material.id}/clone" class="btn btn-sm btn-outline-info" onclick="return confirm('Are you sure you want to duplicate this material record?');">
                        <i class="bi bi-files me-1"></i> Duplicate
                    </a>
                    <button onclick="showDeleteMaterialConfirmation(${material.id}, '${(material.material_name || '').replace(/'/g, "\\'")}')" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash me-1"></i> Delete
                    </button>
                </div>
            </td>
        `;
    }
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

// Open View Material Modal
function openViewMaterialModal(materialId) {
    const modal = document.getElementById('viewMaterialModal');
    const content = document.getElementById('view-material-content');
    
    modal.classList.remove('hidden');
    content.innerHTML = `
        <div class="flex items-center justify-center py-12">
            <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    `;
    
    fetch(`/admin/construction-materials/${materialId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const material = data.material;
        const billUrl = material.bill_attachment ? `/storage/${material.bill_attachment}` : null;
        const photoUrl = material.delivery_photo ? `/storage/${material.delivery_photo}` : null;
        
        content.innerHTML = `
            <div class="row g-3">
                <div class="col-lg-8">
                    <div class="card mb-3">
                        <div class="card-header"><strong>Material &amp; Project</strong></div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Material Name:</strong><div>${material.material_name || ''}</div></div>
                                <div class="col-md-3"><strong>Category:</strong><div>${material.material_category || ''}</div></div>
                                <div class="col-md-3"><strong>Unit:</strong><div>${material.unit || ''}</div></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Project:</strong><div>${material.project_name || ''}</div></div>
                                <div class="col-md-6"><strong>Work Type:</strong><div>${material.work_type || ''}</div></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Delivery Site:</strong><div>${material.delivery_site || ''}</div></div>
                                <div class="col-md-3"><strong>Delivered By:</strong><div>${material.delivered_by || ''}</div></div>
                                <div class="col-md-3"><strong>Received By:</strong><div>${material.received_by || ''}</div></div>
                            </div>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-header"><strong>Usage &amp; Approval</strong></div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Quantity Received:</strong><div>${parseFloat(material.quantity_received || 0).toFixed(2)} ${material.unit || ''}</div></div>
                                <div class="col-md-4"><strong>Quantity Used:</strong><div>${parseFloat(material.quantity_used || 0).toFixed(2)} ${material.unit || ''}</div></div>
                                <div class="col-md-4"><strong>Remaining:</strong><div>${parseFloat(material.quantity_remaining || 0).toFixed(2)} ${material.unit || ''}</div></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-4"><strong>Wastage:</strong><div>${parseFloat(material.wastage_quantity || 0).toFixed(2)} ${material.unit || ''}</div></div>
                                <div class="col-md-4"><strong>Status:</strong><div><span class="badge bg-secondary">${material.status || ''}</span></div></div>
                                <div class="col-md-4"><strong>Delivery Date:</strong><div>${material.delivery_date || ''}</div></div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6"><strong>Approved By:</strong><div>${material.approved_by || ''}</div></div>
                                <div class="col-md-6"><strong>Approval Date:</strong><div>${material.approval_date || ''}</div></div>
                            </div>
                            <div class="mb-2"><strong>Usage Purpose:</strong><p class="mb-0">${material.usage_purpose || ''}</p></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card mb-3">
                        <div class="card-header"><strong>Financial</strong></div>
                        <div class="card-body">
                            <div class="mb-2"><strong>Rate per Unit:</strong><div>${parseFloat(material.rate_per_unit || 0).toFixed(2)}</div></div>
                            <div class="mb-2"><strong>Total Cost:</strong><div>${parseFloat(material.total_cost || 0).toFixed(2)}</div></div>
                            <div class="mb-2"><strong>Bill Number:</strong><div>${material.bill_number || ''}</div></div>
                            <div class="mb-2"><strong>Bill Date:</strong><div>${material.bill_date || ''}</div></div>
                            <div class="mb-2"><strong>Payment Status:</strong><div>${material.payment_status || ''}</div></div>
                            <div class="mb-2"><strong>Payment Mode:</strong><div>${material.payment_mode || ''}</div></div>
                            ${material.expense ? `<div class="mb-2"><strong>Linked Expense Entry:</strong><div><a href="/admin/expenses/${material.expense.id}" class="text-primary">View Expense Entry</a><div class="text-muted small">Created: ${material.expense.created_at}</div></div></div>` : (material.payment_status === 'Paid' ? `<div class="mb-2"><div class="alert alert-info small mb-0"><i class="bi bi-info-circle"></i> Expense entry will be created automatically when payment status is set to "Paid".</div></div>` : '')}
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-header"><strong>Supplier</strong></div>
                        <div class="card-body">
                            <div class="mb-2"><strong>Name:</strong><div>${material.supplier_name || ''}</div></div>
                            <div class="mb-2"><strong>Contact:</strong><div>${material.supplier_contact || ''}</div></div>
                        </div>
                    </div>
                    <div class="card mb-3">
                        <div class="card-header"><strong>Attachments</strong></div>
                        <div class="card-body">
                            <div class="mb-2"><strong>Bill Attachment:</strong><div>${billUrl ? `<a href="${billUrl}" target="_blank">View Bill</a>` : '<span class="text-muted">Not uploaded</span>'}</div></div>
                            <div class="mb-2"><strong>Delivery Photo:</strong><div>${photoUrl ? `<a href="${photoUrl}" target="_blank">View Photo</a>` : '<span class="text-muted">Not uploaded</span>'}</div></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 d-flex justify-content-end gap-2">
                <button onclick="closeViewMaterialModal(); openEditMaterialModal(${material.id})" class="btn btn-primary">Edit</button>
                <button onclick="closeViewMaterialModal()" class="btn btn-secondary">Close</button>
            </div>
        `;
    })
    .catch(error => {
        console.error('Error loading material:', error);
        content.innerHTML = `<div class="text-center py-12"><p class="text-red-600 mb-4">Failed to load material details. Please try again.</p><button onclick="closeViewMaterialModal()" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700">Close</button></div>`;
    });
}

// Close View Material Modal
function closeViewMaterialModal() {
    document.getElementById('viewMaterialModal').classList.add('hidden');
    setTimeout(() => {
        document.getElementById('view-material-content').innerHTML = `
            <div class="flex items-center justify-center py-12">
                <svg class="animate-spin h-8 w-8 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        `;
    }, 300);
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('materialModal').classList.contains('hidden')) {
            closeMaterialModal();
        }
        if (!document.getElementById('viewMaterialModal').classList.contains('hidden')) {
            closeViewMaterialModal();
        }
    }
});

// Close modal when clicking outside
document.getElementById('materialModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeMaterialModal();
    }
});

document.getElementById('viewMaterialModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeViewMaterialModal();
    }
});

// Delete Material Confirmation Functions
function showDeleteMaterialConfirmation(materialId, materialName) {
    deleteMaterialId = materialId;
    document.getElementById('delete-material-name').textContent = materialName;
    document.getElementById('deleteMaterialConfirmationModal').classList.remove('hidden');
}

function closeDeleteMaterialConfirmation() {
    document.getElementById('deleteMaterialConfirmationModal').classList.add('hidden');
    deleteMaterialId = null;
}

function confirmDeleteMaterial() {
    if (!deleteMaterialId) return;
    
    const materialIdToDelete = deleteMaterialId;
    const row = document.querySelector(`tr[data-material-id="${materialIdToDelete}"]`);
    
    // Disable delete button
    const deleteBtn = event.target;
    deleteBtn.disabled = true;
    deleteBtn.textContent = 'Deleting...';
    
    fetch(`/admin/construction-materials/${materialIdToDelete}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        closeDeleteMaterialConfirmation();
        showNotification('Material deleted successfully', 'success');
        
        // Remove row with animation
        if (row) {
            row.style.transition = 'opacity 0.3s, transform 0.3s';
            row.style.opacity = '0';
            row.style.transform = 'translateX(-20px)';
            setTimeout(() => {
                row.remove();
                
                // Check if table is empty
                const tbody = document.querySelector('table tbody');
                if (tbody && tbody.children.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="10" class="text-center text-muted py-3">
                                No records found.
                            </td>
                        </tr>
                    `;
                }
            }, 300);
        } else {
            // Fallback: reload page if row not found
            window.location.reload();
        }
    })
    .catch(error => {
        console.error('Error deleting material:', error);
        showNotification(error.message || 'Failed to delete material', 'error');
        deleteBtn.disabled = false;
        deleteBtn.textContent = 'Delete';
    });
}

// Close delete modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('deleteMaterialConfirmationModal').classList.contains('hidden')) {
            closeDeleteMaterialConfirmation();
        }
    }
});

// Close delete modal when clicking outside
document.getElementById('deleteMaterialConfirmationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteMaterialConfirmation();
    }
});

// Filter functions
// Debounced filter function for performance
const applyFiltersDebounced = window.debounce ? window.debounce(applyFilters, 300) : applyFilters;

function applyFilters() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    const params = new URLSearchParams();
    
    for (const [key, value] of formData.entries()) {
        if (value) {
            params.append(key, value);
        }
    }
    
    const tbody = document.getElementById('materialsTableBody');
    const tfoot = document.getElementById('materialsTableFooter');
    const pagination = document.getElementById('materialsPagination');
    const summary = document.getElementById('materialsSummary');
    
    if (tbody) tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">Loading...</td></tr>';
    if (tfoot) tfoot.innerHTML = '';
    
    // Check cache first
    const cacheKey = 'filter_' + params.toString();
    const cached = window.requestCache ? window.requestCache.get(cacheKey) : null;
    if (cached) {
        if (tbody) tbody.innerHTML = cached.tbody || '';
        if (tfoot) tfoot.innerHTML = cached.tfoot || '';
        if (pagination) pagination.innerHTML = cached.pagination || '';
        if (summary) summary.innerHTML = cached.summary || '';
        return;
    }
    
    fetch(`{{ route('admin.construction-materials.index') }}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(html => {
                console.error('Server returned HTML instead of JSON:', html.substring(0, 500));
                throw new Error('Server returned HTML instead of JSON. This usually means a validation error or authentication issue.');
            });
        }
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (!data) {
            console.error('No data received');
            if (tbody) tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">No data received from server.</td></tr>';
            return;
        }
        
        if (tbody) {
            if (!data.html) {
                console.error('No HTML in response:', data);
                tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">Invalid response format.</td></tr>';
                return;
            }
            
            // Extract tbody and tfoot from the HTML using a temporary table
            // Note: tbody and tfoot can only exist inside a table, so we need to create a temp table
            const tempTable = document.createElement('table');
            tempTable.innerHTML = data.html;
            
            const newTbody = tempTable.querySelector('tbody');
            const newTfoot = tempTable.querySelector('tfoot');
            
            console.log('Temp table HTML:', tempTable.innerHTML.substring(0, 200));
            console.log('Found tbody:', newTbody);
            console.log('Found tfoot:', newTfoot);
            
            if (newTbody) {
                // Directly set innerHTML from the tbody
                tbody.innerHTML = newTbody.innerHTML;
                console.log('Updated tbody with', tbody.querySelectorAll('tr').length, 'rows');
            } else {
                console.warn('No tbody found in response HTML');
                tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-3">No records found.</td></tr>';
            }
            
            // Handle tfoot - find or create it
            const table = tbody.closest('table');
            if (table) {
                let existingTfoot = table.querySelector('tfoot#materialsTableFooter');
                
                if (newTfoot) {
                    if (existingTfoot) {
                        existingTfoot.innerHTML = newTfoot.innerHTML;
                    } else {
                        // Create tfoot if it doesn't exist
                        existingTfoot = document.createElement('tfoot');
                        existingTfoot.id = 'materialsTableFooter';
                        existingTfoot.innerHTML = newTfoot.innerHTML;
                        table.appendChild(existingTfoot);
                    }
                } else {
                    // Remove tfoot if no data or empty
                    if (existingTfoot) {
                        const rowCount = tbody.querySelectorAll('tr').length;
                        const isEmpty = rowCount === 0 || (rowCount === 1 && tbody.querySelector('td[colspan]'));
                        if (isEmpty) {
                            existingTfoot.remove();
                        }
                    }
                }
            }
        }
        if (pagination) {
            pagination.innerHTML = data.pagination || '';
            attachPaginationListeners();
        }
        if (summary) {
            if (data.summary) {
                summary.innerHTML = `<div class="card mb-4 shadow-sm"><div class="card-body py-3">${data.summary}</div></div>`;
            } else {
                summary.innerHTML = '';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (tbody) tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">Error loading data. Please refresh the page.</td></tr>';
    });
}

function resetFilters() {
    document.getElementById('filter_material_name').value = '';
    document.getElementById('filter_supplier_id').value = '';
    document.getElementById('filter_project_name').value = '';
    document.getElementById('filter_purchased_by_id').value = '';
    document.getElementById('filter_from_date').value = '';
    document.getElementById('filter_to_date').value = '';
    applyFilters();
}

// Handle pagination links with AJAX
function handlePaginationClick(e) {
    let paginationLink = null;
    
    if (e.target.tagName === 'A' && e.target.href) {
        if ((e.target.closest('.pagination') || e.target.closest('#materialsPagination')) && e.target.href.includes('construction-materials')) {
            paginationLink = e.target;
        }
    }
    
    if (!paginationLink) {
        const link = e.target.closest('a');
        if (link && link.href && link.href.includes('construction-materials')) {
            if (link.closest('.pagination') || link.closest('#materialsPagination')) {
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
        const materialName = document.getElementById('filter_material_name')?.value;
        const supplierId = document.getElementById('filter_supplier_id')?.value;
        const projectName = document.getElementById('filter_project_name')?.value;
        const purchasedById = document.getElementById('filter_purchased_by_id')?.value;
        const fromDate = document.getElementById('filter_from_date')?.value;
        const toDate = document.getElementById('filter_to_date')?.value;
        
        if (materialName) params.set('material_name', materialName);
        if (supplierId) params.set('supplier_id', supplierId);
        if (projectName) params.set('project_name', projectName);
        if (purchasedById) params.set('purchased_by_id', purchasedById);
        if (fromDate) params.set('from_date', fromDate);
        if (toDate) params.set('to_date', toDate);
        
        const tbody = document.getElementById('materialsTableBody');
        const tfoot = document.getElementById('materialsTableFooter');
        const pagination = document.getElementById('materialsPagination');
        const summary = document.getElementById('materialsSummary');
        
        if (tbody) tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-muted">Loading...</td></tr>';
        if (tfoot) tfoot.innerHTML = '';
        
        fetch(`${url.pathname}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                return response.text().then(html => {
                    console.error('Server returned HTML instead of JSON:', html.substring(0, 500));
                    throw new Error('Server returned HTML instead of JSON. This usually means a validation error or authentication issue.');
                });
            }
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (tbody) {
                if (!data.html) {
                    tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">Invalid response format.</td></tr>';
                    return;
                }
                
                // Extract tbody and tfoot from the HTML using a temporary table
                // Note: tbody and tfoot can only exist inside a table, so we need to create a temp table
                const tempTable = document.createElement('table');
                tempTable.innerHTML = data.html;
                
                const newTbody = tempTable.querySelector('tbody');
                const newTfoot = tempTable.querySelector('tfoot');
                
                if (newTbody) {
                    // Directly set innerHTML from the tbody
                    tbody.innerHTML = newTbody.innerHTML;
                } else {
                    tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted py-3">No records found.</td></tr>';
                }
                
                // Handle tfoot - find or create it
                const table = tbody.closest('table');
                if (table) {
                    let existingTfoot = table.querySelector('tfoot#materialsTableFooter');
                    
                    if (newTfoot) {
                        if (existingTfoot) {
                            existingTfoot.innerHTML = newTfoot.innerHTML;
                        } else {
                            // Create tfoot if it doesn't exist
                            existingTfoot = document.createElement('tfoot');
                            existingTfoot.id = 'materialsTableFooter';
                            existingTfoot.innerHTML = newTfoot.innerHTML;
                            table.appendChild(existingTfoot);
                        }
                    } else {
                        // Remove tfoot if no data or empty
                        if (existingTfoot) {
                            const rowCount = tbody.querySelectorAll('tr').length;
                            const isEmpty = rowCount === 0 || (rowCount === 1 && tbody.querySelector('td[colspan]'));
                            if (isEmpty) {
                                existingTfoot.remove();
                            }
                        }
                    }
                }
            }
            if (pagination) {
                pagination.innerHTML = data.pagination || '';
                attachPaginationListeners();
            }
            if (summary) {
                if (data.summary) {
                    summary.innerHTML = `<div class="card mb-4 shadow-sm"><div class="card-body py-3">${data.summary}</div></div>`;
                } else {
                    summary.innerHTML = '';
                }
            }
            
            if (tbody) {
                tbody.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (tbody) tbody.innerHTML = '<tr><td colspan="10" class="text-center py-4 text-danger">Error loading data.</td></tr>';
        });
    }
}

function attachPaginationListeners() {
    const paginationContainer = document.getElementById('materialsPagination');
    if (paginationContainer) {
        paginationContainer.removeEventListener('click', handlePaginationClick);
        paginationContainer.addEventListener('click', handlePaginationClick);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    attachPaginationListeners();
});

document.addEventListener('click', handlePaginationClick);
</script>
@endpush
@endsection


