@extends('admin.layout')

@section('title', 'Construction Materials')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Construction Materials</h1>
        <small class="text-muted">Manage all materials received on site</small>
    </div>
    <div>
        <a href="{{ route('admin.construction-materials.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Add Material
        </a>
        <a href="{{ route('admin.construction-materials.export', request()->query()) }}" class="btn btn-success ms-2">
            <i class="bi bi-file-earmark-excel"></i> Export Excel
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <strong>Search &amp; Filter</strong>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.construction-materials.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Material Name</label>
                <select name="material_name" class="form-select">
                    <option value="">All materials</option>
                    @foreach($materialNames as $materialName)
                        <option value="{{ $materialName->name }}" {{ request('material_name') === $materialName->name ? 'selected' : '' }}>
                            {{ $materialName->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Supplier</label>
                <select name="supplier_name" class="form-select">
                    <option value="">All suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->name }}" {{ request('supplier_name') === $supplier->name ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Project</label>
                <select name="project_name" class="form-select">
                    <option value="">All projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->name }}" {{ request('project_name') === $project->name ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Purchased / Payment By</label>
                <select name="purchased_by_id" class="form-select">
                    <option value="">All</option>
                    @foreach($purchasedBies as $person)
                        <option value="{{ $person->id }}" {{ request('purchased_by_id') == $person->id ? 'selected' : '' }}>{{ $person->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">From Delivery Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">To Delivery Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
            </div>
            <div class="col-md-12 d-flex justify-content-end mt-2">
                <a href="{{ route('admin.construction-materials.index') }}" class="btn btn-outline-secondary me-2">Reset</a>
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
    </div>
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
            <tbody>
                @forelse($materials as $material)
                    <tr>
                        <td>{{ $material->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $material->material_name }}</div>
                            <small class="text-muted">{{ $material->material_category }}</small>
                        </td>
                        <td>{{ $material->project_name }}</td>
                        <td>{{ $material->supplier_name }}</td>
                        <td>{{ number_format($material->quantity_received, 2) }} {{ $material->unit }}</td>
                        <td>{{ number_format($material->quantity_used, 2) }} {{ $material->unit }}</td>
                        <td>{{ number_format($material->quantity_remaining, 2) }} {{ $material->unit }}</td>
                        <td>{{ number_format($material->total_cost, 2) }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $material->status }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.construction-materials.show', $material) }}" class="btn btn-sm btn-outline-info">View</a>
                            <a href="{{ route('admin.construction-materials.edit', $material) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.construction-materials.destroy', $material) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this record?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-3">
                            No records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($materials->hasPages())
        <div class="card-footer">
            {{ $materials->links() }}
        </div>
    @endif
</div>
@endsection


