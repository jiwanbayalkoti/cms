@extends('admin.layout')

@section('title', 'Edit Completed Work')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Edit Completed Work</h1>
    <a href="{{ route('admin.completed-works.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

<form action="{{ route('admin.completed-works.update', $completed_work) }}" method="POST" id="completedWorkForm">
    @csrf
    @method('PUT')
    
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Work Information</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Project *</label>
                    <select name="project_id" id="project_id" class="form-select" required>
                        <option value="">Select Project</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" {{ old('project_id', $completed_work->project_id) == $project->id ? 'selected' : '' }}>
                                {{ $project->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Work Type *</label>
                    <select name="work_type" id="work_type" class="form-select" required onchange="updateUOM()">
                        <option value="">Select Work Type</option>
                        <option value="PCC" {{ old('work_type', $completed_work->work_type) == 'PCC' ? 'selected' : '' }}>PCC (Plain Cement Concrete)</option>
                        <option value="Soling" {{ old('work_type', $completed_work->work_type) == 'Soling' ? 'selected' : '' }}>Soling / Base</option>
                        <option value="Masonry" {{ old('work_type', $completed_work->work_type) == 'Masonry' ? 'selected' : '' }}>Masonry / Wall</option>
                        <option value="Plaster" {{ old('work_type', $completed_work->work_type) == 'Plaster' ? 'selected' : '' }}>Plaster</option>
                        <option value="Concrete" {{ old('work_type', $completed_work->work_type) == 'Concrete' ? 'selected' : '' }}>Concrete</option>
                    </select>
                    @error('work_type')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-semibold">Quantity Input Method *</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="quantity_input_method" id="input_method_dimensions" value="dimensions" {{ old('quantity_input_method', $completed_work->quantity_input_method ?? 'dimensions') == 'dimensions' ? 'checked' : '' }} onchange="toggleInputMethod()">
                        <label class="btn btn-outline-primary" for="input_method_dimensions">
                            <i class="bi bi-rulers me-1"></i> Calculate from Dimensions (L × B × H)
                        </label>
                        <input type="radio" class="btn-check" name="quantity_input_method" id="input_method_direct" value="direct" {{ old('quantity_input_method', $completed_work->quantity_input_method ?? 'dimensions') == 'direct' ? 'checked' : '' }} onchange="toggleInputMethod()">
                        <label class="btn btn-outline-primary" for="input_method_direct">
                            <i class="bi bi-123 me-1"></i> Enter Total Quantity Directly
                        </label>
                    </div>
                </div>
                
                <div id="dimensionsSection" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Length (m) <span id="lengthRequired">*</span></label>
                        <input type="number" name="length" id="length" class="form-control" step="0.001" min="0" value="{{ old('length', $completed_work->length) }}" oninput="calculateQuantity()">
                        @error('length')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Width/Breadth (m) <span id="widthRequired">*</span></label>
                        <input type="number" name="width" id="width" class="form-control" step="0.001" min="0" value="{{ old('width', $completed_work->width) }}" oninput="calculateQuantity()">
                        @error('width')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Height/Thickness (m) <span id="heightRequired">*</span></label>
                        <input type="number" name="height" id="height" class="form-control" step="0.001" min="0" value="{{ old('height', $completed_work->height) }}" oninput="calculateQuantity()">
                        <small class="text-muted">Height for walls, Thickness for PCC/Soling/Plaster</small>
                        @error('height')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Quantity <span id="quantityRequired">*</span></label>
                    <input type="number" name="quantity" id="quantity" class="form-control" step="0.001" min="0" value="{{ old('quantity', $completed_work->quantity) }}" oninput="handleQuantityInput()">
                    <small class="text-muted" id="quantityHint">Auto-calculated based on work type</small>
                    @error('quantity')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unit of Measurement</label>
                    <input type="text" name="uom" id="uom" class="form-control" value="{{ old('uom', $completed_work->uom) }}" readonly>
                    <small class="text-muted">Auto-set based on work type</small>
                    @error('uom')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Work Date *</label>
                    <input type="date" name="work_date" class="form-control" value="{{ old('work_date', $completed_work->work_date->format('Y-m-d')) }}" required>
                    @error('work_date')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="2">{{ old('description', $completed_work->description) }}</textarea>
                    <small class="text-muted">Auto-generated from dimensions if left empty</small>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('admin.completed-works.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Update Completed Work</button>
    </div>
</form>

@push('scripts')
<script>
function toggleInputMethod() {
    const inputMethod = document.querySelector('input[name="quantity_input_method"]:checked').value;
    const dimensionsSection = document.getElementById('dimensionsSection');
    const lengthInput = document.getElementById('length');
    const widthInput = document.getElementById('width');
    const heightInput = document.getElementById('height');
    const quantityInput = document.getElementById('quantity');
    const lengthRequired = document.getElementById('lengthRequired');
    const widthRequired = document.getElementById('widthRequired');
    const heightRequired = document.getElementById('heightRequired');
    const quantityRequired = document.getElementById('quantityRequired');
    
    if (inputMethod === 'dimensions') {
        // Show dimensions section, make them required
        dimensionsSection.style.display = 'block';
        lengthInput.required = true;
        widthInput.required = true;
        heightInput.required = true;
        quantityInput.readOnly = true;
        lengthRequired.style.display = 'inline';
        widthRequired.style.display = 'inline';
        heightRequired.style.display = 'inline';
        quantityRequired.style.display = 'none';
        calculateQuantity();
    } else {
        // Hide dimensions section, make them optional
        dimensionsSection.style.display = 'none';
        lengthInput.required = false;
        widthInput.required = false;
        heightInput.required = false;
        quantityInput.readOnly = false;
        lengthRequired.style.display = 'none';
        widthRequired.style.display = 'none';
        heightRequired.style.display = 'none';
        quantityRequired.style.display = 'inline';
        document.getElementById('quantityHint').textContent = 'Enter total quantity directly';
    }
}

function handleQuantityInput() {
    const inputMethod = document.querySelector('input[name="quantity_input_method"]:checked').value;
    if (inputMethod === 'direct') {
        // User is entering quantity directly, no calculation needed
        const quantityInput = document.getElementById('quantity');
        if (quantityInput.value && parseFloat(quantityInput.value) > 0) {
            document.getElementById('quantityHint').textContent = 'Total quantity entered';
        }
    }
}

function updateUOM() {
    const workType = document.getElementById('work_type').value;
    const uomInput = document.getElementById('uom');
    const quantityHint = document.getElementById('quantityHint');
    const inputMethod = document.querySelector('input[name="quantity_input_method"]:checked')?.value || 'dimensions';
    
    switch(workType) {
        case 'PCC':
        case 'Concrete':
            uomInput.value = 'm³';
            if (inputMethod === 'dimensions') {
                quantityHint.textContent = 'Volume (m³) = Length × Width × Height';
            }
            break;
        case 'Soling':
            uomInput.value = 'm³';
            if (inputMethod === 'dimensions') {
                quantityHint.textContent = 'Volume (m³) = Length × Width × Height';
            }
            break;
        case 'Masonry':
            uomInput.value = 'm³';
            if (inputMethod === 'dimensions') {
                quantityHint.textContent = 'Volume (m³) = Length × Height × Thickness';
            }
            break;
        case 'Plaster':
            uomInput.value = 'm²';
            if (inputMethod === 'dimensions') {
                quantityHint.textContent = 'Area (m²) = Length × Width';
            }
            break;
        default:
            uomInput.value = '';
            quantityHint.textContent = 'Select work type first';
    }
    
    if (inputMethod === 'dimensions') {
        calculateQuantity();
    }
}

function calculateQuantity() {
    const inputMethod = document.querySelector('input[name="quantity_input_method"]:checked')?.value || 'dimensions';
    
    if (inputMethod !== 'dimensions') {
        return; // Don't calculate if direct input mode
    }
    
    const workType = document.getElementById('work_type').value;
    const length = parseFloat(document.getElementById('length').value) || 0;
    const width = parseFloat(document.getElementById('width').value) || 0;
    const height = parseFloat(document.getElementById('height').value) || 0;
    const quantityInput = document.getElementById('quantity');
    const descriptionInput = document.getElementById('description');
    
    if (!workType || length <= 0 || width <= 0 || height <= 0) {
        quantityInput.value = '';
        return;
    }
    
    let quantity = 0;
    let description = '';
    
    switch(workType) {
        case 'PCC':
        case 'Concrete':
            quantity = length * width * height;
            description = `${workType} ${length}m × ${width}m × ${height}m`;
            break;
        case 'Soling':
            quantity = length * width * height;
            description = `Soling ${length}m × ${width}m × ${height}m`;
            break;
        case 'Masonry':
            quantity = length * height * width; // width is thickness for masonry
            description = `Masonry Wall ${length}m × ${height}m × ${width}m`;
            break;
        case 'Plaster':
            quantity = length * width; // Area for plaster
            description = `Plaster ${length}m × ${width}m`;
            break;
    }
    
    quantityInput.value = quantity.toFixed(3);
    
    // Auto-fill description if empty
    if (!descriptionInput.value) {
        descriptionInput.value = description;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleInputMethod();
    updateUOM();
});
</script>
@endpush
@endsection
