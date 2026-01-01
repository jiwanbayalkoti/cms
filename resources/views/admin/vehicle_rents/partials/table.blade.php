@forelse($vehicleRents as $rent)
    <tr data-rent-id="{{ $rent->id }}">
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
        <td>{{ $rent->supplier->name ?? '—' }}</td>
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
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewVehicleRentModal({{ $rent->id }})" class="btn btn-outline-primary btn-sm" title="View">
                    <i class="bi bi-eye"></i>
                </button>
                <button onclick="openEditVehicleRentModal({{ $rent->id }})" class="btn btn-outline-warning btn-sm" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeleteVehicleRentConfirmation({{ $rent->id }}, '{{ addslashes($rent->vehicle_number ?? 'Vehicle') }}', '{{ $rent->rent_date->format('Y-m-d') }}')" class="btn btn-outline-danger btn-sm" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="12" class="text-center py-4">
            <p class="text-muted mb-0">No vehicle rent records found.</p>
        </td>
    </tr>
@endforelse

@if($vehicleRents->count() > 0)
<tfoot>
    <tr class="table-primary">
        <td colspan="7" class="text-end"><strong>Subtotals:</strong></td>
        <td class="text-end"><strong>{{ number_format($vehicleRents->sum('total_amount'), 2) }}</strong></td>
        <td class="text-end"><strong>{{ number_format($vehicleRents->sum('paid_amount'), 2) }}</strong></td>
        <td class="text-end"><strong>{{ number_format($vehicleRents->sum('balance_amount'), 2) }}</strong></td>
        <td colspan="2"></td>
    </tr>
    @if(request('supplier_id') && isset($totalAdvancePayments) && $totalAdvancePayments > 0)
    <tr class="table-info">
        <td colspan="9" class="text-end"><strong>Less: Advance Payments</strong></td>
        <td class="text-end"><strong class="text-info">({{ number_format($totalAdvancePayments, 2) }})</strong></td>
        <td colspan="2"></td>
    </tr>
    <tr class="table-success">
        <td colspan="9" class="text-end"><strong>Net Balance (After Advance Payments):</strong></td>
        <td class="text-end"><strong class="{{ $netBalance > 0 ? 'text-danger' : ($netBalance < 0 ? 'text-success' : 'text-secondary') }}">{{ number_format($netBalance, 2) }}</strong></td>
        <td colspan="2"></td>
    </tr>
    @endif
</tfoot>
@endif

