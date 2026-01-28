@extends('admin.layout')
@section('title', 'New Measurement Book')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">New Measurement Book</h1>
    <a href="{{ route('admin.measurement-books.index') }}" class="btn btn-outline-secondary">Back</a>
</div>

<form method="POST" action="{{ route('admin.measurement-books.store') }}" id="mbForm">
    @csrf
    <div class="card mb-4">
        <div class="card-header"><strong>Header</strong></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Project *</label>
                    <select name="project_id" class="form-select" required>
                        <option value="">Select Project</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}">{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Contract No</label>
                    <input type="text" name="contract_no" class="form-control" placeholder="e.g. NA/NO.2FESBN/NCB/...">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Measurement Date *</label>
                    <input type="date" name="measurement_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Title (optional)</label>
                    <input type="text" name="title" class="form-control" placeholder="e.g. Measurement Book - Jan 2025">
                </div>
                <div class="col-md-6">
                    <label class="form-label">L, W, H Unit</label>
                    <select name="dimension_unit" id="dimensionUnit" class="form-select">
                        @foreach($dimensionUnits as $key => $label)
                            <option value="{{ $key }}" {{ $key === 'ft' ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Measurement Book – Works</strong>
            <button type="button" class="btn btn-sm btn-primary" onclick="addMainWorkRow()"><i class="bi bi-plus"></i> Add Main Work</button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered mb-0" id="mbItemsTable">
                    <thead class="table-light">
                        <tr>
                            <th>SN</th>
                            <th>Works (Description) *</th>
                            <th>no</th>
                            <th id="mbThL">Length (ft)</th>
                            <th id="mbThB">Breadth (ft)</th>
                            <th id="mbThH">Height (ft)</th>
                            <th>Quantity</th>
                            <th>Total qty</th>
                            <th>Unit *</th>
                            <th width="80"></th>
                        </tr>
                    </thead>
                    <tbody id="mbItemsBody">
                        <tr class="mb-item-row mb-main-work" data-row-index="0">
                            <td class="sn-cell">1</td>
                            <td><textarea name="items[0][works]" class="form-control form-control-sm" rows="2" required placeholder="Main work description"></textarea></td>
                            <td class="main-work-field"><input type="number" name="items[0][no]" class="form-control form-control-sm" value="1" step="0.0001" min="0" onchange="calcQty(this)"></td>
                            <td class="main-work-field"><input type="number" name="items[0][length_ft]" class="form-control form-control-sm" step="0.0001" min="0" onchange="calcQty(this)"></td>
                            <td class="main-work-field"><input type="number" name="items[0][breadth_ft]" class="form-control form-control-sm" step="0.0001" min="0" onchange="calcQty(this)"></td>
                            <td class="main-work-field"><input type="number" name="items[0][height_ft]" class="form-control form-control-sm" step="0.0001" min="0" onchange="calcQty(this)"></td>
                            <td class="main-work-field"><input type="number" name="items[0][quantity]" class="form-control form-control-sm" step="0.0001" min="0" readonly placeholder="auto"></td>
                            <td><input type="number" name="items[0][total_qty]" class="form-control form-control-sm main-total-qty" step="0.0001" min="0" readonly placeholder="auto"></td>
                            <td class="main-work-field"><input type="text" name="items[0][unit]" class="form-control form-control-sm" placeholder="sft/cuft" list="uomList" onchange="calcQty(this)"></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success mb-1" onclick="addSubWorkRow(this)" title="Add Sub Work"><i class="bi bi-plus-circle"></i></button>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMbRow(this)" title="Remove">×</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <datalist id="uomList"><option value="sft"><option value="cuft"><option value="rft"><option value="cft"><option value="sqft"><option value="sqm"><option value="cum"></datalist>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Save Measurement Book</button>
        <a href="{{ route('admin.measurement-books.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<style>
.mb-main-work {
    background-color: #f8f9fa;
    font-weight: 500;
}
.mb-sub-work {
    background-color: #ffffff;
}
.mb-sub-work td:first-child {
    padding-left: 2rem !important;
}
.mb-sub-work .works-cell {
    padding-left: 1.5rem;
}
.mb-sub-work .works-cell::before {
    content: "└─ ";
    color: #6c757d;
    margin-right: 0.5rem;
}
</style>

@push('scripts')
<script>
let mbRowIndex = 1;
const DIM_LABELS = { ft:'ft', m:'m', in:'in', cm:'cm' };

function applyDimensionUnit() {
    const u = (document.getElementById('dimensionUnit') || {}).value || 'ft';
    const label = DIM_LABELS[u] || u;
    const el = document.getElementById('mbThL'); if (el) el.textContent = 'Length (' + label + ')';
    const e2 = document.getElementById('mbThB'); if (e2) e2.textContent = 'Breadth (' + label + ')';
    const e3 = document.getElementById('mbThH'); if (e3) e3.textContent = 'Height (' + label + ')';
}

document.getElementById('dimensionUnit').addEventListener('change', function(){ applyDimensionUnit(); recalcAllRows(); });
document.addEventListener('DOMContentLoaded', function(){ 
    applyDimensionUnit(); 
    recalcAllRows();
    // Toggle main work fields based on existing sub-works
    document.querySelectorAll('.mb-main-work').forEach(row => {
        toggleMainWorkFields(row);
    });
});

function recalcAllRows() {
    document.querySelectorAll('#mbItemsBody .mb-item-row').forEach(function(tr){
        var inp = tr.querySelector('input[name*="[length_ft]"]'); if (inp) calcQty(inp);
    });
    updateMainWorkTotals();
}

function addMainWorkRow() {
    const tbody = document.getElementById('mbItemsBody');
    const firstRow = tbody.querySelector('.mb-main-work');
    const tr = document.createElement('tr');
    tr.className = 'mb-item-row mb-main-work';
    tr.setAttribute('data-row-index', mbRowIndex);
    
    tr.innerHTML = `
        <td class="sn-cell">${mbRowIndex + 1}</td>
        <td><textarea name="items[${mbRowIndex}][works]" class="form-control form-control-sm" rows="2" required placeholder="Main work description"></textarea></td>
        <td class="main-work-field"><input type="number" name="items[${mbRowIndex}][no]" class="form-control form-control-sm" value="1" step="0.0001" min="0" onchange="calcQty(this)"></td>
        <td class="main-work-field"><input type="number" name="items[${mbRowIndex}][length_ft]" class="form-control form-control-sm" step="0.0001" min="0" onchange="calcQty(this)"></td>
        <td class="main-work-field"><input type="number" name="items[${mbRowIndex}][breadth_ft]" class="form-control form-control-sm" step="0.0001" min="0" onchange="calcQty(this)"></td>
        <td class="main-work-field"><input type="number" name="items[${mbRowIndex}][height_ft]" class="form-control form-control-sm" step="0.0001" min="0" onchange="calcQty(this)"></td>
        <td class="main-work-field"><input type="number" name="items[${mbRowIndex}][quantity]" class="form-control form-control-sm" step="0.0001" min="0" readonly placeholder="auto"></td>
        <td><input type="number" name="items[${mbRowIndex}][total_qty]" class="form-control form-control-sm main-total-qty" step="0.0001" min="0" readonly placeholder="auto"></td>
        <td class="main-work-field"><input type="text" name="items[${mbRowIndex}][unit]" class="form-control form-control-sm" placeholder="sft/cuft" list="uomList" onchange="calcQty(this)"></td>
        <td>
            <button type="button" class="btn btn-sm btn-success mb-1" onclick="addSubWorkRow(this)" title="Add Sub Work"><i class="bi bi-plus-circle"></i></button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMbRow(this)" title="Remove">×</button>
        </td>
    `;
    
    tbody.appendChild(tr);
    mbRowIndex++;
    renumberMbRows();
    recalcAllRows();
    toggleMainWorkFields(tr);
}

function addSubWorkRow(btn) {
    const mainRow = btn.closest('tr.mb-main-work');
    const tbody = document.getElementById('mbItemsBody');
    const allRows = Array.from(tbody.querySelectorAll('.mb-item-row'));
    const parentIndex = allRows.indexOf(mainRow);
    
    // Find the last sub-work of this main work, or insert after main work
    let insertAfter = mainRow;
    let nextRow = mainRow.nextElementSibling;
    while (nextRow && nextRow.classList.contains('mb-sub-work')) {
        const nextParentIndex = parseInt(nextRow.querySelector('input[name*="[parent_id]"]')?.value || '-1');
        if (nextParentIndex === parentIndex) {
            insertAfter = nextRow;
            nextRow = nextRow.nextElementSibling;
        } else {
            break;
        }
    }
    
    const tr = document.createElement('tr');
    tr.className = 'mb-item-row mb-sub-work';
    tr.setAttribute('data-row-index', mbRowIndex);
    tr.setAttribute('data-parent-index', parentIndex);
    
    tr.innerHTML = `
        <td class="sn-cell"></td>
        <td class="works-cell"><textarea name="items[${mbRowIndex}][works]" class="form-control form-control-sm" rows="2" placeholder="Sub work description"></textarea></td>
        <td><input type="number" name="items[${mbRowIndex}][no]" class="form-control form-control-sm" value="1" step="0.0001" min="0" onchange="calcQty(this)"></td>
        <td><input type="number" name="items[${mbRowIndex}][length_ft]" class="form-control form-control-sm" step="0.0001" min="0" onchange="calcQty(this)"></td>
        <td><input type="number" name="items[${mbRowIndex}][breadth_ft]" class="form-control form-control-sm" step="0.0001" min="0" onchange="calcQty(this)"></td>
        <td><input type="number" name="items[${mbRowIndex}][height_ft]" class="form-control form-control-sm" step="0.0001" min="0" onchange="calcQty(this)"></td>
        <td><input type="number" name="items[${mbRowIndex}][quantity]" class="form-control form-control-sm sub-qty" step="0.0001" min="0" readonly placeholder="auto"></td>
        <td><input type="number" name="items[${mbRowIndex}][total_qty]" class="form-control form-control-sm" step="0.0001" min="0" readonly placeholder="auto"></td>
        <td><input type="text" name="items[${mbRowIndex}][unit]" class="form-control form-control-sm" placeholder="sft/cuft" list="uomList" onchange="calcQty(this)"></td>
        <td>
            <input type="hidden" name="items[${mbRowIndex}][parent_id]" value="${parentIndex}">
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeMbRow(this)" title="Remove">×</button>
        </td>
    `;
    
    insertAfter.insertAdjacentElement('afterend', tr);
    mbRowIndex++;
    renumberMbRows();
    recalcAllRows();
    updateMainWorkTotals();
    toggleMainWorkFields(mainRow);
}

function removeMbRow(btn) {
    const row = btn.closest('tr');
    const tbody = document.getElementById('mbItemsBody');
    
    // If it's a main work, remove all its sub-works too
    if (row.classList.contains('mb-main-work')) {
        const rowIndex = row.getAttribute('data-row-index');
        let nextRow = row.nextElementSibling;
        while (nextRow && nextRow.classList.contains('mb-sub-work') && nextRow.getAttribute('data-parent-index') === rowIndex) {
            const toRemove = nextRow;
            nextRow = nextRow.nextElementSibling;
            toRemove.remove();
        }
    }
    
    // Don't allow removing if it's the last main work
    const mainWorks = tbody.querySelectorAll('.mb-main-work');
    if (mainWorks.length <= 1 && row.classList.contains('mb-main-work')) {
        alert('At least one main work is required.');
        return;
    }
    
    const wasMainWork = row.classList.contains('mb-main-work');
    const parentRow = wasMainWork ? null : row.closest('tbody').querySelector(`.mb-main-work[data-row-index="${row.getAttribute('data-parent-index')}"]`);
    
    row.remove();
    renumberMbRows();
    updateMainWorkTotals();
    
    // If sub-work was removed, check if parent main work needs fields shown
    if (parentRow) {
        toggleMainWorkFields(parentRow);
    }
}

function renumberMbRows() {
    let sn = 1;
    document.querySelectorAll('#mbItemsBody .mb-main-work').forEach((tr) => {
        tr.querySelector('.sn-cell').textContent = sn;
        sn++;
    });
    
    // Update all input names to be sequential and fix parent_id references
    let index = 0;
    const allRows = Array.from(document.querySelectorAll('#mbItemsBody .mb-item-row'));
    
    allRows.forEach((tr) => {
        tr.setAttribute('data-row-index', index);
        
        tr.querySelectorAll('input, textarea, select').forEach(el => {
            if (el.name && el.name.includes('[items]')) {
                el.name = el.name.replace(/items\[\d+\]/, 'items[' + index + ']');
            }
        });
        
        // Update parent_id in hidden input if it's a sub-work
        const parentIdInput = tr.querySelector('input[name*="[parent_id]"]');
        if (parentIdInput && tr.classList.contains('mb-sub-work')) {
            const oldParentIndex = tr.getAttribute('data-parent-index');
            // Find the parent row by its old data-row-index and get its new index
            const parentRow = allRows.find(r => r.getAttribute('data-row-index') === oldParentIndex || (r.classList.contains('mb-main-work') && r === tr.previousElementSibling));
            if (parentRow) {
                const newParentIndex = allRows.indexOf(parentRow);
                parentIdInput.value = newParentIndex;
                tr.setAttribute('data-parent-index', newParentIndex);
            }
        }
        
        index++;
    });
    
    mbRowIndex = index;
}

function toggleMainWorkFields(mainRow) {
    if (!mainRow || !mainRow.classList.contains('mb-main-work')) return;
    
    const mainIndex = parseInt(mainRow.getAttribute('data-row-index'));
    let hasSubWorks = false;
    let nextRow = mainRow.nextElementSibling;
    while (nextRow && nextRow.classList.contains('mb-sub-work')) {
        const parentId = parseInt(nextRow.querySelector('input[name*="[parent_id]"]')?.value || '-1');
        if (parentId === mainIndex) {
            hasSubWorks = true;
            break;
        }
        nextRow = nextRow.nextElementSibling;
    }
    
    const fields = mainRow.querySelectorAll('.main-work-field');
    const noInput = mainRow.querySelector('input[name*="[no]"]');
    const lengthInput = mainRow.querySelector('input[name*="[length_ft]"]');
    const breadthInput = mainRow.querySelector('input[name*="[breadth_ft]"]');
    const heightInput = mainRow.querySelector('input[name*="[height_ft]"]');
    const quantityInput = mainRow.querySelector('input[name*="[quantity]"]');
    const unitInput = mainRow.querySelector('input[name*="[unit]"]');
    
    if (hasSubWorks) {
        // Hide fields and remove required
        fields.forEach(field => field.style.display = 'none');
        if (noInput) noInput.removeAttribute('required');
        if (lengthInput) lengthInput.removeAttribute('required');
        if (breadthInput) breadthInput.removeAttribute('required');
        if (heightInput) heightInput.removeAttribute('required');
        if (quantityInput) quantityInput.removeAttribute('required');
        if (unitInput) unitInput.removeAttribute('required');
    } else {
        // Show fields and add required
        fields.forEach(field => field.style.display = '');
        if (noInput) noInput.setAttribute('required', 'required');
        if (lengthInput) lengthInput.setAttribute('required', 'required');
        if (breadthInput) breadthInput.setAttribute('required', 'required');
        if (heightInput) heightInput.setAttribute('required', 'required');
        if (quantityInput) quantityInput.setAttribute('required', 'required');
        if (unitInput) unitInput.setAttribute('required', 'required');
    }
}

function updateMainWorkTotals() {
    document.querySelectorAll('#mbItemsBody .mb-main-work').forEach(mainRow => {
        const mainIndex = parseInt(mainRow.getAttribute('data-row-index'));
        const totalQtyInput = mainRow.querySelector('.main-total-qty');
        let total = 0;
        
        // Sum all sub-work quantities
        let nextRow = mainRow.nextElementSibling;
        while (nextRow && nextRow.classList.contains('mb-sub-work')) {
            const parentId = parseInt(nextRow.querySelector('input[name*="[parent_id]"]')?.value || '-1');
            if (parentId === mainIndex) {
                const subQtyInput = nextRow.querySelector('.sub-qty');
                if (subQtyInput && subQtyInput.value) {
                    total += parseFloat(subQtyInput.value) || 0;
                }
                nextRow = nextRow.nextElementSibling;
            } else {
                break;
            }
        }
        
        // If no sub-works, use main work's own quantity
        if (total === 0) {
            const mainQtyInput = mainRow.querySelector('input[name*="[quantity]"]');
            total = parseFloat(mainQtyInput?.value || 0);
        }
        
        if (totalQtyInput) {
            totalQtyInput.value = total.toFixed(4);
        }
    });
}

function calcQty(inp) {
    const row = inp.closest('tr');
    if (!row) return;
    const no = parseFloat(row.querySelector('input[name*="[no]"]').value) || 1;
    const L = parseFloat(row.querySelector('input[name*="[length_ft]"]').value) || 0;
    const B = parseFloat(row.querySelector('input[name*="[breadth_ft]"]').value) || 0;
    const H = parseFloat(row.querySelector('input[name*="[height_ft]"]').value) || 0;
    const unit = (row.querySelector('input[name*="[unit]"]').value || '').toLowerCase();
    
    const qtyInp = row.querySelector('input[name*="[quantity]"]');
    const tqInp = row.querySelector('input[name*="[total_qty]"]');
    
    // Only calculate if length and width have values greater than 0
    if (L > 0 && B > 0) {
        var areaUnits = ['sft','rft','sqft','sqm'];
        var volumeUnits = ['cuft','cft','cum','cubic'];
        var isArea = areaUnits.indexOf(unit) >= 0;
        var isVolume = volumeUnits.indexOf(unit) >= 0;
        
        var q = 0;
        if (isArea) {
            // Area calculation: no × L × B
            q = no * L * B;
        } else if (isVolume) {
            // Volume calculation: no × L × B × H (H must be > 0)
            if (H > 0) {
                q = no * L * B * H;
            } else {
                q = 0; // Height required for volume
            }
        } else {
            // Default: treat as volume if unit is not specified or unknown
            if (H > 0) {
                q = no * L * B * H;
            } else {
                q = no * L * B; // Fallback to area if no height
            }
        }
        
        if (qtyInp) qtyInp.value = q.toFixed(4);
        if (tqInp && !row.classList.contains('mb-main-work')) {
            tqInp.value = q.toFixed(4);
        }
    } else {
        // Clear quantity if length or width is missing
        if (qtyInp) qtyInp.value = '';
        if (tqInp && !row.classList.contains('mb-main-work')) {
            tqInp.value = '';
        }
    }
    
    // Update main work totals if this is a sub-work
    if (row.classList.contains('mb-sub-work')) {
        updateMainWorkTotals();
    }
}
</script>
@endpush
@endsection
