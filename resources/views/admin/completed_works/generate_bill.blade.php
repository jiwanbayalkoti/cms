@extends('admin.layout')

@section('title', 'Generate Bill from Completed Works')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Generate Bill from Completed Works</h1>
        <p class="text-muted mb-0">Select completed works to generate a bill with actual quantities</p>
    </div>
    <a href="{{ route('admin.completed-works.index', request()->all()) }}" class="btn btn-outline-secondary">Back to Completed Works</a>
</div>

<form action="{{ route('admin.completed-works.generate-bill.store') }}" method="POST" id="generateBillForm">
    @csrf
    
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Bill Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Project *</label>
                    <select name="project_id" id="project_id" class="form-select" required>
                        <option value="">Select Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', request('project_id')) == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Bill Title *</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', 'Bill from Completed Works - ' . now()->format('Y-m-d')) }}" required>
                    @error('title')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Version</label>
                    <input type="text" name="version" class="form-control" value="{{ old('version', '1.0') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">MB Number</label>
                    <input type="text" name="mb_number" class="form-control" value="{{ old('mb_number') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">MB Date</label>
                    <input type="date" name="mb_date" class="form-control" value="{{ old('mb_date', date('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Overhead %</label>
                    <input type="number" name="overhead_percent" class="form-control" step="0.01" min="0" max="100" value="{{ old('overhead_percent', '10') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contingency %</label>
                    <input type="number" name="contingency_percent" class="form-control" step="0.01" min="0" max="100" value="{{ old('contingency_percent', '5') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Add Work Items</h5>
            <div>
                <button type="button" class="btn btn-sm btn-primary" onclick="showAddWorkForm()">
                    <i class="bi bi-plus-circle me-1"></i> Add Work Directly
                </button>
            </div>
        </div>
        <div class="card-body">
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="select-tab" data-bs-toggle="tab" data-bs-target="#select-works" type="button" role="tab">
                        Select from Completed Works
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="add-tab" data-bs-toggle="tab" data-bs-target="#add-directly" type="button" role="tab">
                        Add Work Directly
                    </button>
                </li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane fade show active" id="select-works" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Select Completed Works</h6>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAll()">Select All</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="deselectAll()">Deselect All</button>
                        </div>
                    </div>
        <div class="card-body">
            @if($completedWorks->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="selectAllCheckbox" onchange="toggleAll(this)">
                                </th>
                                <th>Work Date</th>
                                <th>Work Type</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Unit</th>
                                <th>Project</th>
                                <th>Linked Bill Item</th>
                            </tr>
                        </thead>
                        <tbody id="worksTableBody">
                            @foreach($completedWorks as $index => $work)
                                <tr data-work-id="{{ $work->id }}">
                                    <td>
                                        <input type="checkbox" 
                                               name="completed_work_ids[]" 
                                               value="{{ $work->id }}" 
                                               class="work-checkbox"
                                               onchange="toggleWorkRow(this)"
                                               {{ old('completed_work_ids') && in_array($work->id, old('completed_work_ids')) ? 'checked' : '' }}>
                                    </td>
                                    <td>{{ $work->work_date->format('Y-m-d') }}</td>
                                    <td><span class="badge bg-info">{{ $work->work_type }}</span></td>
                                    <td>{{ $work->description }}</td>
                                    <td class="text-end"><strong>{{ number_format($work->quantity, 3) }}</strong></td>
                                    <td>{{ $work->uom }}</td>
                                    <td>{{ $work->project->name ?? '—' }}</td>
                                    <td>
                                        @if($work->billItem)
                                            <small class="text-muted">{{ $work->billItem->description }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                    @else
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            No completed works available for billing. All works may have already been billed or no works match the current filters.
                        </div>
                    @endif
                </div>
                <div class="tab-pane fade" id="add-directly" role="tabpanel">
                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <label class="form-label small">Work Type *</label>
                            <select id="directWorkType" class="form-select form-select-sm" onchange="updateDirectUOM()">
                                <option value="">Select Work Type</option>
                                <option value="PCC">PCC</option>
                                <option value="Soling">Soling</option>
                                <option value="Masonry">Masonry</option>
                                <option value="Plaster">Plaster</option>
                                <option value="Concrete">Concrete</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Length (m) *</label>
                            <input type="number" id="directLength" class="form-control form-control-sm" step="0.001" min="0" oninput="calculateDirectQuantity()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Width (m) *</label>
                            <input type="number" id="directWidth" class="form-control form-control-sm" step="0.001" min="0" oninput="calculateDirectQuantity()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Height (m) *</label>
                            <input type="number" id="directHeight" class="form-control form-control-sm" step="0.001" min="0" oninput="calculateDirectQuantity()">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Quantity</label>
                            <input type="number" id="directQuantity" class="form-control form-control-sm" step="0.001" readonly>
                            <small class="text-muted" id="directUOM"></small>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small">&nbsp;</label>
                            <button type="button" class="btn btn-sm btn-success w-100" onclick="addDirectWork()">
                                <i class="bi bi-plus"></i> Add
                            </button>
                        </div>
                    </div>
                    <div id="directWorksList" class="mt-3">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Work Type</th>
                                    <th>Dimensions</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th width="50"></th>
                                </tr>
                            </thead>
                            <tbody id="directWorksBody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4" id="billItemsCard" style="display: none;">
        <div class="card-header">
            <h5 class="mb-0">Bill Items (Edit quantities and rates if needed)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Unit Rate</th>
                            <th>Wastage %</th>
                            <th>Tax %</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody id="billItemsBody">
                        <!-- Items will be populated by JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('admin.completed-works.index', request()->all()) }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-success" id="generateBtn" disabled>
            <i class="bi bi-receipt me-1"></i> Generate Bill
        </button>
    </div>
</form>

@push('scripts')
<script>
const completedWorks = @json($completedWorksJson);
let directWorks = []; // Store directly added works

function updateDirectUOM() {
    const workType = document.getElementById('directWorkType').value;
    const uomSpan = document.getElementById('directUOM');
    
    switch(workType) {
        case 'PCC':
        case 'Concrete':
        case 'Soling':
        case 'Masonry':
            uomSpan.textContent = 'm³';
            break;
        case 'Plaster':
            uomSpan.textContent = 'm²';
            break;
        default:
            uomSpan.textContent = '';
    }
    calculateDirectQuantity();
}

function calculateDirectQuantity() {
    const workType = document.getElementById('directWorkType').value;
    const length = parseFloat(document.getElementById('directLength').value) || 0;
    const width = parseFloat(document.getElementById('directWidth').value) || 0;
    const height = parseFloat(document.getElementById('directHeight').value) || 0;
    const quantityInput = document.getElementById('directQuantity');
    
    if (!workType || length <= 0 || width <= 0 || height <= 0) {
        quantityInput.value = '';
        return;
    }
    
    let quantity = 0;
    if (workType === 'Plaster') {
        quantity = length * width; // Area
    } else {
        quantity = length * width * height; // Volume
    }
    
    quantityInput.value = quantity.toFixed(3);
}

function addDirectWork() {
    const workType = document.getElementById('directWorkType').value;
    const length = parseFloat(document.getElementById('directLength').value) || 0;
    const width = parseFloat(document.getElementById('directWidth').value) || 0;
    const height = parseFloat(document.getElementById('directHeight').value) || 0;
    const quantity = parseFloat(document.getElementById('directQuantity').value) || 0;
    const uom = document.getElementById('directUOM').textContent;
    
    if (!workType || length <= 0 || width <= 0 || height <= 0) {
        alert('Please fill all fields correctly.');
        return;
    }
    
    const work = {
        id: 'direct_' + Date.now(),
        work_type: workType,
        description: `${workType} ${length}m × ${width}m × ${height}m`,
        quantity: quantity,
        uom: uom,
        length: length,
        width: width,
        height: height,
        bill_item: null,
        category: workType,
        subcategory: null,
        remarks: ''
    };
    
    directWorks.push(work);
    renderDirectWorksList();
    
    // Clear form
    document.getElementById('directWorkType').value = '';
    document.getElementById('directLength').value = '';
    document.getElementById('directWidth').value = '';
    document.getElementById('directHeight').value = '';
    document.getElementById('directQuantity').value = '';
    document.getElementById('directUOM').textContent = '';
    
    updateBillItems();
}

function renderDirectWorksList() {
    const tbody = document.getElementById('directWorksBody');
    tbody.innerHTML = '';
    
    directWorks.forEach((work, index) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${work.work_type}</td>
            <td>${work.length}m × ${work.width}m × ${work.height}m</td>
            <td>${work.quantity}</td>
            <td>${work.uom}</td>
            <td>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeDirectWork(${index})">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function removeDirectWork(index) {
    directWorks.splice(index, 1);
    renderDirectWorksList();
    updateBillItems();
}

function toggleAll(checkbox) {
    document.querySelectorAll('.work-checkbox').forEach(cb => {
        cb.checked = checkbox.checked;
        toggleWorkRow(cb);
    });
}

function selectAll() {
    document.querySelectorAll('.work-checkbox').forEach(cb => {
        cb.checked = true;
        toggleWorkRow(cb);
    });
    document.getElementById('selectAllCheckbox').checked = true;
}

function deselectAll() {
    document.querySelectorAll('.work-checkbox').forEach(cb => {
        cb.checked = false;
        toggleWorkRow(cb);
    });
    document.getElementById('selectAllCheckbox').checked = false;
}

function toggleWorkRow(checkbox) {
    const row = checkbox.closest('tr');
    if (checkbox.checked) {
        row.classList.add('table-active');
    } else {
        row.classList.remove('table-active');
    }
    updateBillItems();
}

function updateBillItems() {
    const selectedCheckboxes = document.querySelectorAll('.work-checkbox:checked');
    const billItemsBody = document.getElementById('billItemsBody');
    const billItemsCard = document.getElementById('billItemsCard');
    const generateBtn = document.getElementById('generateBtn');
    
    const totalItems = selectedCheckboxes.length + directWorks.length;
    
    if (totalItems === 0) {
        billItemsCard.style.display = 'none';
        generateBtn.disabled = true;
        return;
    }
    
    billItemsCard.style.display = 'block';
    generateBtn.disabled = false;
    
    billItemsBody.innerHTML = '';
    let itemIndex = 0;
    
    // Add selected completed works
    selectedCheckboxes.forEach((checkbox) => {
        const workId = parseInt(checkbox.value);
        const work = completedWorks.find(w => w.id === workId);
        if (!work) return;
        
        const billItem = work.bill_item;
        const unitRate = billItem ? billItem.unit_rate : 0;
        const wastagePercent = billItem ? billItem.wastage_percent : 0;
        const taxPercent = billItem ? billItem.tax_percent : 0;
        const effectiveQty = work.quantity * (1 + wastagePercent / 100);
        const totalAmount = effectiveQty * unitRate;
        const netAmount = totalAmount * (1 + taxPercent / 100);
        
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="hidden" name="items[${itemIndex}][completed_work_id]" value="${work.id}">
                <input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm" value="${work.description}" required>
            </td>
            <td>
                <small>${work.category}${work.subcategory ? ' - ' + work.subcategory : ''}</small>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm" step="0.001" min="0" value="${work.quantity}" required onchange="calculateRowAmount(this)">
            </td>
            <td>${work.uom}</td>
            <td>
                <input type="number" name="items[${itemIndex}][unit_rate]" class="form-control form-control-sm" step="0.01" min="0" value="${unitRate}" required onchange="calculateRowAmount(this)">
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][wastage_percent]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="${wastagePercent}" onchange="calculateRowAmount(this)">
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][tax_percent]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="${taxPercent}" onchange="calculateRowAmount(this)">
            </td>
            <td class="text-end">
                <strong class="row-amount">${netAmount.toFixed(2)}</strong>
            </td>
        `;
        billItemsBody.appendChild(row);
        itemIndex++;
    });
    
    // Add directly added works
    directWorks.forEach((work) => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <input type="hidden" name="items[${itemIndex}][direct_work]" value="1">
                <input type="hidden" name="items[${itemIndex}][work_type]" value="${work.work_type}">
                <input type="hidden" name="items[${itemIndex}][length]" value="${work.length}">
                <input type="hidden" name="items[${itemIndex}][width]" value="${work.width}">
                <input type="hidden" name="items[${itemIndex}][height]" value="${work.height}">
                <input type="hidden" name="items[${itemIndex}][uom]" value="${work.uom}">
                <input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm" value="${work.description}" required>
            </td>
            <td>
                <small>${work.work_type}</small>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm" step="0.001" min="0" value="${work.quantity}" required onchange="calculateRowAmount(this)">
            </td>
            <td>${work.uom}</td>
            <td>
                <input type="number" name="items[${itemIndex}][unit_rate]" class="form-control form-control-sm" step="0.01" min="0" value="0" required onchange="calculateRowAmount(this)">
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][wastage_percent]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="0" onchange="calculateRowAmount(this)">
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][tax_percent]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="0" onchange="calculateRowAmount(this)">
            </td>
            <td class="text-end">
                <strong class="row-amount">0.00</strong>
            </td>
        `;
        billItemsBody.appendChild(row);
        itemIndex++;
    });
}

function calculateRowAmount(input) {
    const row = input.closest('tr');
    const quantity = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
    const unitRate = parseFloat(row.querySelector('input[name*="[unit_rate]"]').value) || 0;
    const wastagePercent = parseFloat(row.querySelector('input[name*="[wastage_percent]"]').value) || 0;
    const taxPercent = parseFloat(row.querySelector('input[name*="[tax_percent]"]').value) || 0;
    
    const effectiveQty = quantity * (1 + wastagePercent / 100);
    const totalAmount = effectiveQty * unitRate;
    const netAmount = totalAmount * (1 + taxPercent / 100);
    
    row.querySelector('.row-amount').textContent = netAmount.toFixed(2);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateBillItems();
});
</script>
@endpush
@endsection

