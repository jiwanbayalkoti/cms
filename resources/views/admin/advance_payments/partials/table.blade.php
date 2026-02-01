<tbody id="advancePaymentsTableBody">
    @forelse($advancePayments as $payment)
    <tr data-advance-payment-id="{{ $payment->id }}">
        <td>{{ ($advancePayments->currentPage() - 1) * $advancePayments->perPage() + $loop->iteration }}</td>
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
            <div class="d-flex gap-1 text-nowrap">
                <button onclick="openViewAdvancePaymentModal({{ $payment->id }})" class="btn btn-sm btn-outline-primary" title="View">
                    <i class="bi bi-eye"></i>
                </button>
                <button onclick="openEditAdvancePaymentModal({{ $payment->id }})" class="btn btn-sm btn-outline-warning" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button onclick="showDeleteAdvancePaymentConfirmation({{ $payment->id }}, '{{ addslashes($payment->supplier->name ?? 'N/A') }}')" class="btn btn-sm btn-outline-danger" title="Delete">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="9" class="text-center text-muted py-4">No advance payments found.</td>
    </tr>
    @endforelse
</tbody>

