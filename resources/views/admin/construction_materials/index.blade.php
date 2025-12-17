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
        <form method="GET" action="{{ route('admin.construction-materials.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Material Name</label>
                <select name="material_name" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All materials</option>
                    @foreach($materialNames as $materialName)
                        <option value="{{ $materialName->name }}" {{ request('material_name') === $materialName->name ? 'selected' : '' }}>
                            {{ $materialName->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Supplier</label>
                <select name="supplier_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Project</label>
                <select name="project_name" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->name }}" {{ request('project_name') === $project->name ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Purchased / Payment By</label>
                <select name="purchased_by_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All</option>
                    @foreach($purchasedBies as $person)
                        <option value="{{ $person->id }}" {{ request('purchased_by_id') == $person->id ? 'selected' : '' }}>{{ $person->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">From Delivery Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm" onchange="this.form.submit()">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">To Delivery Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm" onchange="this.form.submit()">
            </div>
            <div class="col-md-12 mt-2">
                <a href="{{ route('admin.construction-materials.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset Filters
                </a>
                <small class="text-muted ms-2">Filters apply automatically when changed</small>
            </div>
        </form>
    </div>
</div>

@if($materials->count() > 0)
<div class="card mb-4 shadow-sm">
    <div class="card-body py-3">
        <div class="row g-3 mb-0">
            <div class="col-md-3">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <div class="flex-shrink-0">
                        <i class="bi bi-cash-stack text-primary fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <small class="text-muted d-block mb-1">Total Cost</small>
                        <h5 class="mb-0 text-primary fw-bold">{{ number_format($totalCost, 2) }}</h5>
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
                        <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Material Payments</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="d-flex align-items-center p-3 bg-light rounded">
                    <div class="flex-shrink-0">
                        <i class="bi bi-{{ $netBalance > 0 ? 'exclamation-triangle-fill text-danger' : 'check-circle-fill text-success' }} fs-4"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <small class="text-muted d-block mb-1">Remaining Balance</small>
                        <h5 class="mb-0 text-{{ $netBalance > 0 ? 'danger' : 'success' }} fw-bold">{{ number_format($netBalance ?? $totalCost, 2) }}</h5>
                        @if($netBalance > 0)
                            <small class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i>Outstanding</small>
                        @else
                            <small class="text-success"><i class="bi bi-check-circle me-1"></i>All Paid</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

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
                            <div class="d-flex gap-1 justify-content-end">
                                <a href="{{ route('admin.construction-materials.show', $material) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                <a href="{{ route('admin.construction-materials.edit', $material) }}" class="btn btn-sm btn-outline-warning">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                <a href="{{ route('admin.construction-materials.clone', $material) }}" class="btn btn-sm btn-outline-info" onclick="return confirm('Are you sure you want to duplicate this material record?');">
                                    <i class="bi bi-files me-1"></i> Duplicate
                                </a>
                                <form action="{{ route('admin.construction-materials.destroy', $material) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </div>
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
            @if($materials->count() > 0)
            <tfoot>
                <tr class="table-secondary">
                    <td colspan="7" class="text-end"><strong>Total:</strong></td>
                    <td class="text-end"><strong>{{ number_format($totalCost, 2) }}</strong></td>
                    <td colspan="2"></td>
                </tr>
                @if(request('supplier_id') && isset($totalAdvancePayments) && $totalAdvancePayments > 0)
                <tr class="table-info">
                    <td colspan="7" class="text-end"><strong>Less: Advance Payments</strong></td>
                    <td class="text-end"><strong class="text-info">({{ number_format($totalAdvancePayments, 2) }})</strong></td>
                    <td colspan="2"></td>
                </tr>
                <tr class="table-success">
                    <td colspan="7" class="text-end"><strong>Net Balance (After Advance Payments):</strong></td>
                    <td class="text-end"><strong class="{{ $netBalance > 0 ? 'text-danger' : ($netBalance < 0 ? 'text-success' : 'text-secondary') }}">{{ number_format($netBalance, 2) }}</strong></td>
                    <td colspan="2"></td>
                </tr>
                @endif
            </tfoot>
            @endif
        </table>
    </div>
    <x-pagination :paginator="$materials" wrapper-class="card-footer" />
</div>
@endsection


