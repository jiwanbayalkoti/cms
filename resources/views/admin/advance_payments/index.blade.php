@extends('admin.layout')

@section('title', 'Advance Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
<div>
        <h1 class="h3 mb-0">Advance Payments</h1>
        <p class="text-muted mb-0">Manage advance payments for vehicle rent and materials</p>
    </div>
    <a href="{{ route('admin.advance-payments.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add Advance Payment
    </a>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.advance-payments.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Project</label>
                <select name="project_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Projects</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Payment Type</label>
                <select name="payment_type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="vehicle_rent" {{ request('payment_type') == 'vehicle_rent' ? 'selected' : '' }}>Vehicle Rent</option>
                    <option value="material_payment" {{ request('payment_type') == 'material_payment' ? 'selected' : '' }}>Material Payment</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Supplier</label>
                <select name="supplier_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Start Date</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">End Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-12 mt-2">
                <a href="{{ route('admin.advance-payments.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset Filters
                </a>
            </div>
        </form>
    </div>
</div>

@if($advancePayments->count() > 0)
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
                        <h5 class="mb-0">Rs. {{ number_format($totalAmount, 2) }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
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
                <tbody>
                    @foreach($advancePayments as $payment)
                    <tr>
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
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.advance-payments.show', $payment) }}" class="btn btn-outline-info btn-sm" title="View">
                                    <i class="bi bi-eye me-1"></i> View
                                </a>
                                <a href="{{ route('admin.advance-payments.edit', $payment) }}" class="btn btn-outline-primary btn-sm" title="Edit">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                                <form action="{{ route('admin.advance-payments.destroy', $payment) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this advance payment?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Delete">
                                        <i class="bi bi-trash me-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $advancePayments->links() }}
        </div>
    </div>
</div>
@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="bi bi-inbox fs-1 text-muted"></i>
        <p class="text-muted mt-3">No advance payments found.</p>
        <a href="{{ route('admin.advance-payments.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add First Advance Payment
        </a>
    </div>
</div>
@endif
@endsection
