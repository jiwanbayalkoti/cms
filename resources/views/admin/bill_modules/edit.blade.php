@extends('admin.layout')

@section('title', 'Edit Bill Module')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit Bill: {{ $bill_module->title }}</h1>
    <a href="{{ route('admin.bill-modules.show', $bill_module) }}" class="btn btn-outline-secondary">Back</a>
</div>

<form id="billForm" action="{{ route('admin.bill-modules.update', $bill_module) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="card mb-4">
        <div class="card-header"><strong>Bill Information</strong></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Project *</label>
                    <select name="project_id" class="form-select" required disabled>
                        <option value="{{ $bill_module->project_id }}">{{ $bill_module->project->name }}</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Title *</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $bill_module->title) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Version</label>
                    <input type="text" name="version" class="form-control" value="{{ old('version', $bill_module->version) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">MB Number</label>
                    <input type="text" name="mb_number" class="form-control" value="{{ old('mb_number', $bill_module->mb_number) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">MB Date</label>
                    <input type="date" name="mb_date" class="form-control" value="{{ old('mb_date', $bill_module->mb_date?->format('Y-m-d')) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Overhead %</label>
                    <input type="number" name="overhead_percent" class="form-control" value="{{ old('overhead_percent', $bill_module->aggregate->overhead_percent ?? 10) }}" step="0.01" min="0" max="100">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contingency %</label>
                    <input type="number" name="contingency_percent" class="form-control" value="{{ old('contingency_percent', $bill_module->aggregate->contingency_percent ?? 5) }}" step="0.01" min="0" max="100">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $bill_module->notes) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Bill Items</strong>
            <button type="button" class="btn btn-sm btn-primary" onclick="addItemRow()">
                <i class="bi bi-plus-circle me-1"></i> Add Item
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="itemsTable">
                    <thead>
                        <tr>
                            <th width="5%">S.N.</th>
                            <th width="12%">Category *</th>
                            <th width="10%">Subcategory</th>
                            <th width="20%">Description *</th>
                            <th width="8%">UOM *</th>
                            <th width="8%">Quantity *</th>
                            <th width="7%">Wastage %</th>
                            <th width="8%">Unit Rate *</th>
                            <th width="8%">Tax %</th>
                            <th width="8%">Remarks</th>
                            <th width="6%">Action</th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        @foreach($bill_module->items as $index => $item)
                            <tr class="item-row">
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                    <select name="items[{{ $index }}][bill_category_id]" class="form-select form-select-sm category-select" required onchange="loadSubcategories(this, {{ $index }})">
                                        <option value="">Select Category</option>
                                        @foreach($billCategories as $cat)
                                            <option value="{{ $cat->id }}" {{ $item->bill_category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <select name="items[{{ $index }}][bill_subcategory_id]" class="form-select form-select-sm subcategory-select" id="subcategory_{{ $index }}">
                                        <option value="">Select Subcategory</option>
                                        @if($item->bill_category_id)
                                            @php
                                                $selectedCategory = $billCategories->firstWhere('id', $item->bill_category_id);
                                            @endphp
                                            @if($selectedCategory)
                                                @foreach($selectedCategory->activeSubcategories as $sub)
                                                    <option value="{{ $sub->id }}" {{ $item->bill_subcategory_id == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                                @endforeach
                                            @endif
                                        @endif
                                    </select>
                                </td>
                                <td><input type="text" name="items[{{ $index }}][description]" class="form-control form-control-sm" value="{{ $item->description }}" required></td>
                                <td><input type="text" name="items[{{ $index }}][uom]" class="form-control form-control-sm" value="{{ $item->uom }}" required></td>
                                <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm" step="0.001" min="0" value="{{ $item->quantity }}" required></td>
                                <td><input type="number" name="items[{{ $index }}][wastage_percent]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="{{ $item->wastage_percent }}"></td>
                                <td><input type="number" name="items[{{ $index }}][unit_rate]" class="form-control form-control-sm" step="0.01" min="0" value="{{ $item->unit_rate }}" required></td>
                                <td><input type="number" name="items[{{ $index }}][tax_percent]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="{{ $item->tax_percent }}"></td>
                                <td><input type="text" name="items[{{ $index }}][remarks]" class="form-control form-control-sm" value="{{ $item->remarks }}"></td>
                                <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">×</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <a href="{{ route('admin.bill-modules.show', $bill_module) }}" class="btn btn-secondary me-2">Cancel</a>
        <button type="submit" class="btn btn-primary">Update Bill</button>
    </div>
</form>

<script>
let itemIndex = {{ $bill_module->items->count() }};
const billCategories = @json($billCategories);
const subcategoriesByCategory = @json($subcategoriesData);

function loadSubcategories(selectElement, rowIndex) {
    const categoryId = selectElement.value;
    const subcategorySelect = document.getElementById(`subcategory_${rowIndex}`);
    
    subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
    
    if (categoryId && subcategoriesByCategory[categoryId]) {
        const subcategories = subcategoriesByCategory[categoryId];
        subcategories.forEach(sub => {
            const option = document.createElement('option');
            option.value = sub.id;
            option.textContent = sub.name;
            subcategorySelect.appendChild(option);
        });
    }
}

function addItemRow() {
    const tbody = document.getElementById('itemsBody');
    const row = document.createElement('tr');
    row.className = 'item-row';
    row.innerHTML = `
        <td>${tbody.children.length + 1}</td>
        <td>
            <select name="items[${itemIndex}][bill_category_id]" class="form-select form-select-sm category-select" required onchange="loadSubcategories(this, ${itemIndex})">
                <option value="">Select Category</option>
                ${billCategories.map(cat => `<option value="${cat.id}">${cat.name}</option>`).join('')}
            </select>
        </td>
        <td>
            <select name="items[${itemIndex}][bill_subcategory_id]" class="form-select form-select-sm subcategory-select" id="subcategory_${itemIndex}">
                <option value="">Select Subcategory</option>
            </select>
        </td>
        <td><input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm" required></td>
        <td><input type="text" name="items[${itemIndex}][uom]" class="form-control form-control-sm" required></td>
        <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm" step="0.001" min="0" required></td>
        <td><input type="number" name="items[${itemIndex}][wastage_percent]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="0"></td>
        <td><input type="number" name="items[${itemIndex}][unit_rate]" class="form-control form-control-sm" step="0.01" min="0" required></td>
        <td><input type="number" name="items[${itemIndex}][tax_percent]" class="form-control form-control-sm" step="0.01" min="0" max="100" value="13"></td>
        <td><input type="text" name="items[${itemIndex}][remarks]" class="form-control form-control-sm"></td>
        <td><button type="button" class="btn btn-sm btn-danger" onclick="removeRow(this)">×</button></td>
    `;
    tbody.appendChild(row);
    itemIndex++;
    updateRowNumbers();
}

// Load subcategories for existing items on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.category-select').forEach((select, index) => {
        if (select.value) {
            loadSubcategories(select, index);
        }
    });
});

function removeRow(btn) {
    if (document.querySelectorAll('.item-row').length <= 1) {
        alert('At least one item is required');
        return;
    }
    btn.closest('tr').remove();
    updateRowNumbers();
}

function updateRowNumbers() {
    document.querySelectorAll('.item-row').forEach((row, index) => {
        row.querySelector('td:first-child').textContent = index + 1;
    });
}
</script>
@endsection


