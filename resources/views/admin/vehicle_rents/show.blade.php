@extends('admin.layout')

@section('title', 'Vehicle Rent Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Vehicle Rent Details</h1>
        <p class="text-muted mb-0">{{ $vehicleRent->vehicle_type }} - {{ $vehicleRent->start_location }} to {{ $vehicleRent->destination_location }}</p>
    </div>
    <div>
        <a href="{{ route('admin.vehicle-rents.edit', $vehicleRent) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        <a href="{{ route('admin.vehicle-rents.index') }}" class="btn btn-outline-secondary">Back to List</a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Vehicle & Trip Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Rent Date:</th>
                        <td>{{ $vehicleRent->rent_date->format('F d, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Vehicle Type:</th>
                        <td>
                            <span class="badge bg-info">{{ \App\Models\VehicleRent::getVehicleTypes()[$vehicleRent->vehicle_type] ?? $vehicleRent->vehicle_type }}</span>
                        </td>
                    </tr>
                    @if($vehicleRent->vehicle_number)
                        <tr>
                            <th>Vehicle Number:</th>
                            <td>{{ $vehicleRent->vehicle_number }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Start Location:</th>
                        <td><strong>{{ $vehicleRent->start_location }}</strong></td>
                    </tr>
                    <tr>
                        <th>Destination Location:</th>
                        <td><strong>{{ $vehicleRent->destination_location }}</strong></td>
                    </tr>
                    @if($vehicleRent->distance_km)
                        <tr>
                            <th>Distance:</th>
                            <td>{{ number_format($vehicleRent->distance_km, 2) }} km</td>
                        </tr>
                    @endif
                    @if($vehicleRent->hours !== null || $vehicleRent->minutes !== null)
                        <tr>
                            <th>Duration:</th>
                            <td>
                                @if($vehicleRent->hours > 0)
                                    {{ $vehicleRent->hours }} hour{{ $vehicleRent->hours > 1 ? 's' : '' }}
                                @endif
                                @if($vehicleRent->minutes > 0)
                                    {{ $vehicleRent->minutes }} minute{{ $vehicleRent->minutes > 1 ? 's' : '' }}
                                @endif
                                @if(($vehicleRent->hours ?? 0) == 0 && ($vehicleRent->minutes ?? 0) == 0)
                                    —
                                @endif
                            </td>
                        </tr>
                    @endif
                    @if($vehicleRent->rate_type === 'daywise')
                        <tr>
                            <th>Number of Days:</th>
                            <td>
                                {{ $vehicleRent->calculated_days }} day{{ $vehicleRent->calculated_days > 1 ? 's' : '' }}
                                @if($vehicleRent->is_ongoing)
                                    <span class="badge bg-warning ms-2">Ongoing</span>
                                @endif
                            </td>
                        </tr>
                    @elseif($vehicleRent->number_of_days)
                        <tr>
                            <th>Number of Days:</th>
                            <td>{{ $vehicleRent->number_of_days }} day{{ $vehicleRent->number_of_days > 1 ? 's' : '' }}</td>
                        </tr>
                    @endif
                    @if($vehicleRent->driver_name)
                        <tr>
                            <th>Driver Name:</th>
                            <td>{{ $vehicleRent->driver_name }}</td>
                        </tr>
                    @endif
                    @if($vehicleRent->driver_contact)
                        <tr>
                            <th>Driver Contact:</th>
                            <td>{{ $vehicleRent->driver_contact }}</td>
                        </tr>
                    @endif
                    @if($vehicleRent->purpose)
                        <tr>
                            <th>Purpose:</th>
                            <td>{{ $vehicleRent->purpose }}</td>
                        </tr>
                    @endif
                    @if($vehicleRent->project)
                        <tr>
                            <th>Project:</th>
                            <td>{{ $vehicleRent->project->name }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
        
        @if($vehicleRent->notes)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Notes</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $vehicleRent->notes }}</p>
                </div>
            </div>
        @endif
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Rate Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>Rate Type:</th>
                        <td>{{ ucfirst(str_replace('_', ' ', $vehicleRent->rate_type)) }}</td>
                    </tr>
                    @if($vehicleRent->rate_type === 'per_km')
                        @if($vehicleRent->rate_per_km)
                            <tr>
                                <th>Rate per km:</th>
                                <td>{{ number_format($vehicleRent->rate_per_km, 2) }}</td>
                            </tr>
                        @endif
                    @elseif($vehicleRent->rate_type === 'per_hour')
                        @if($vehicleRent->rate_per_hour)
                            <tr>
                                <th>Rate per hour:</th>
                                <td>{{ number_format($vehicleRent->rate_per_hour, 2) }}</td>
                            </tr>
                        @endif
                    @elseif($vehicleRent->rate_type === 'daywise')
                        @if($vehicleRent->rent_start_date)
                            <tr>
                                <th>Rent Start Date:</th>
                                <td>{{ $vehicleRent->rent_start_date->format('F d, Y') }}</td>
                            </tr>
                        @endif
                        @if($vehicleRent->rent_end_date)
                            <tr>
                                <th>Rent End Date:</th>
                                <td>{{ $vehicleRent->rent_end_date->format('F d, Y') }}</td>
                            </tr>
                        @elseif($vehicleRent->is_ongoing)
                            <tr>
                                <th>Rent End Date:</th>
                                <td><span class="badge bg-warning">Ongoing (Till {{ now()->format('F d, Y') }})</span></td>
                            </tr>
                        @endif
                        @if($vehicleRent->rate_per_day)
                            <tr>
                                <th>Rate per day:</th>
                                <td>{{ number_format($vehicleRent->rate_per_day, 2) }}</td>
                            </tr>
                        @endif
                        <tr>
                            <th>Calculated Days:</th>
                            <td><strong>{{ $vehicleRent->calculated_days }} day{{ $vehicleRent->calculated_days > 1 ? 's' : '' }}</strong></td>
                        </tr>
                    @elseif($vehicleRent->rate_type === 'per_quintal')
                        @if($vehicleRent->quantity_quintal)
                            <tr>
                                <th>Quantity (Quintal):</th>
                                <td>{{ number_format($vehicleRent->quantity_quintal, 2) }}</td>
                            </tr>
                        @endif
                        @if($vehicleRent->rate_per_quintal)
                            <tr>
                                <th>Rate per quintal:</th>
                                <td>{{ number_format($vehicleRent->rate_per_quintal, 2) }}</td>
                            </tr>
                        @endif
                    @elseif($vehicleRent->rate_type === 'not_fixed')
                        <tr>
                            <th>Rate Type:</th>
                            <td><span class="badge bg-secondary">Not Fixed</span></td>
                        </tr>
                    @else
                        @if($vehicleRent->fixed_rate)
                            <tr>
                                <th>Fixed Rate:</th>
                                <td>{{ number_format($vehicleRent->fixed_rate, 2) }}</td>
                            </tr>
                        @endif
                    @endif
                </table>
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th>Total Amount:</th>
                        <td class="text-end">
                            <strong class="text-primary">
                                {{ number_format($vehicleRent->is_ongoing ? $vehicleRent->calculated_total_amount : $vehicleRent->total_amount, 2) }}
                            </strong>
                            @if($vehicleRent->is_ongoing)
                                <br><small class="text-muted">Calculated till today</small>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Paid Amount:</th>
                        <td class="text-end">{{ number_format($vehicleRent->paid_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Balance Amount:</th>
                        <td class="text-end">
                            @php
                                $balanceAmount = $vehicleRent->is_ongoing ? $vehicleRent->calculated_balance_amount : $vehicleRent->balance_amount;
                            @endphp
                            <strong class="{{ $balanceAmount > 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($balanceAmount, 2) }}
                            </strong>
                        </td>
                    </tr>
                    <tr>
                        <th>Payment Status:</th>
                        <td>
                            @php
                                $paymentStatus = $vehicleRent->is_ongoing ? $vehicleRent->calculated_payment_status : $vehicleRent->payment_status;
                            @endphp
                            <span class="badge bg-{{ $paymentStatus === 'paid' ? 'success' : ($paymentStatus === 'partial' ? 'warning' : 'danger') }}">
                                {{ ucfirst($paymentStatus) }}
                            </span>
                        </td>
                    </tr>
                    @if($vehicleRent->bankAccount)
                        <tr>
                            <th>Bank Account:</th>
                            <td>{{ $vehicleRent->bankAccount->account_name }}</td>
                        </tr>
                    @endif
                    @if($vehicleRent->payment_date)
                        <tr>
                            <th>Payment Date:</th>
                            <td>{{ $vehicleRent->payment_date->format('F d, Y') }}</td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Record Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    @if($vehicleRent->creator)
                        <tr>
                            <th>Created By:</th>
                            <td>{{ $vehicleRent->creator->name }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $vehicleRent->created_at->format('F d, Y h:i A') }}</td>
                    </tr>
                    @if($vehicleRent->updater)
                        <tr>
                            <th>Updated By:</th>
                            <td>{{ $vehicleRent->updater->name }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Updated At:</th>
                        <td>{{ $vehicleRent->updated_at->format('F d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

