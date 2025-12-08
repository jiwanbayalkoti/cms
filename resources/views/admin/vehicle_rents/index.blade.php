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
        <a href="{{ route('admin.vehicle-rents.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Add Vehicle Rent
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.vehicle-rents.index') }}" class="row g-2 align-items-end">
            <div class="col-md-2">
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
            <div class="col-md-2">
                <label class="form-label small mb-1">Vehicle Type</label>
                <select name="vehicle_type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    @foreach($vehicleTypes as $key => $label)
                        <option value="{{ $key }}" {{ request('vehicle_type') == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Payment Status</label>
                <select name="payment_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Rate Type</label>
                <select name="rate_type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Types</option>
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
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">End Date</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-12 mt-2">
                <a href="{{ route('admin.vehicle-rents.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-clockwise me-1"></i> Reset Filters
                </a>
                <small class="text-muted ms-2">Filters apply automatically when changed</small>
            </div>
        </form>
    </div>
</div>

@if($vehicleRents->count() > 0)
<div class="card mb-4 shadow-sm">
    <div class="card-body py-3">
        <div class="row g-3 mb-0">
            <div class="col-md-4">
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
            <div class="col-md-4">
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
            <div class="col-md-4">
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
        </div>
    </div>
</div>
@endif

<div class="card">
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
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Balance</th>
                        <th>Payment Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehicleRents as $rent)
                        <tr>
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
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.vehicle-rents.show', $rent) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
                                    <a href="{{ route('admin.vehicle-rents.edit', $rent) }}" class="btn btn-outline-warning btn-sm">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.vehicle-rents.destroy', $rent) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="bi bi-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                <p class="text-muted mb-0">No vehicle rent records found.</p>
                                <a href="{{ route('admin.vehicle-rents.create') }}" class="btn btn-primary btn-sm mt-2">Add First Record</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                    @if($vehicleRents->count() > 0)
                    <tfoot>
                        <tr class="table-primary">
                            <td colspan="6" class="text-end"><strong>Totals:</strong></td>
                            <td class="text-end"><strong>{{ number_format($vehicleRents->sum('total_amount'), 2) }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($vehicleRents->sum('paid_amount'), 2) }}</strong></td>
                            <td class="text-end"><strong>{{ number_format($vehicleRents->sum('balance_amount'), 2) }}</strong></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
        
        <x-pagination :paginator="$vehicleRents" :show-info="true" />
    </div>
</div>
@endsection

