@extends('admin.layout')

@section('title', 'Add Completed Work')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Add Completed Work</h1>
    <a href="{{ route('admin.completed-works.index') }}" class="btn btn-outline-secondary">Back to List</a>
</div>

<form action="{{ route('admin.completed-works.store') }}" method="POST" id="completedWorkForm">
    @csrf
    
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
                            <option value="{{ $project->id }}" {{ old('project_id', $selectedProjectId) == $project->id ? 'selected' : '' }}>
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
                        <option value="PCC" {{ old('work_type') == 'PCC' ? 'selected' : '' }}>PCC (Plain Cement Concrete)</option>
                        <option value="Soling" {{ old('work_type') == 'Soling' ? 'selected' : '' }}>Soling / Base</option>
                        <option value="Masonry" {{ old('work_type') == 'Masonry' ? 'selected' : '' }}>Masonry / Wall</option>
                        <option value="Plaster" {{ old('work_type') == 'Plaster' ? 'selected' : '' }}>Plaster</option>
                        <option value="Concrete" {{ old('work_type') == 'Concrete' ? 'selected' : '' }}>Concrete</option>
                    </select>
                    @error('work_type')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Length (m) *</label>
                    <input type="number" name="length" id="length" class="form-control" step="0.001" min="0" value="{{ old('length') }}" required oninput="calculateQuantity()">
                    @error('length')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Width (m) *</label>
                    <input type="number" name="width" id="width" class="form-control" step="0.001" min="0" value="{{ old('width') }}" required oninput="calculateQuantity()">
                    @error('width')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Height/Thickness (m) *</label>
                    <input type="number" name="height" id="height" class="form-control" step="0.001" min="0" value="{{ old('height') }}" required oninput="calculateQuantity()">
                    <small class="text-muted">Height for walls, Thickness for PCC/Soling/Plaster</small>
                    @error('height')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Calculated Quantity</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" step="0.001" min="0" value="{{ old('quantity') }}" readonly>
                    <small class="text-muted" id="quantityHint">Auto-calculated based on work type</small>
                    @error('quantity')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Unit of Measurement</label>
                    <input type="text" name="uom" id="uom" class="form-control" value="{{ old('uom') }}" readonly>
                    <small class="text-muted">Auto-set based on work type</small>
                    @error('uom')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Work Date *</label>
                    <input type="date" name="work_date" class="form-control" value="{{ old('work_date', date('Y-m-d')) }}" required>
                    @error('work_date')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="2">{{ old('description') }}</textarea>
                    <small class="text-muted">Auto-generated from dimensions if left empty</small>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="{{ route('admin.completed-works.index') }}" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Save Completed Work</button>
    </div>
</form>

@push('scripts')
<script>
function updateUOM() {
    const workType = document.getElementById('work_type').value;
    const uomInput = document.getElementById('uom');
    const quantityHint = document.getElementById('quantityHint');
    
    switch(workType) {
        case 'PCC':
        case 'Concrete':
            uomInput.value = 'm³';
            quantityHint.textContent = 'Volume (m³) = Length × Width × Height';
            break;
        case 'Soling':
            uomInput.value = 'm³';
            quantityHint.textContent = 'Volume (m³) = Length × Width × Height';
            break;
        case 'Masonry':
            uomInput.value = 'm³';
            quantityHint.textContent = 'Volume (m³) = Length × Height × Thickness';
            break;
        case 'Plaster':
            uomInput.value = 'm²';
            quantityHint.textContent = 'Area (m²) = Length × Width';
            break;
        default:
            uomInput.value = '';
            quantityHint.textContent = 'Select work type first';
    }
    calculateQuantity();
}

function calculateQuantity() {
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
    updateUOM();
});
</script>
@endpush
@endsection
