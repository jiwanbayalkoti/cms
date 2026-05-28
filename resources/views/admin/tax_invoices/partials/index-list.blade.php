<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead class="table-light">
            <tr>
                <th>Invoice No.</th>
                <th>Date</th>
                <th>Buyer</th>
                <th class="text-end">Grand Total</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
            </tr>
        </thead>
        <tbody id="taxInvoicesTbody">
            @forelse($invoices as $inv)
                <tr>
                    <td class="fw-semibold">{{ $inv->invoice_number }}</td>
                    <td>{{ $inv->invoice_date->format('M d, Y') }}@if($inv->transaction_date_bs)<br><small class="text-muted">BS: {{ $inv->transaction_date_bs }}</small>@endif</td>
                    <td>{{ $inv->buyer_name }}</td>
                    <td class="text-end">Rs. {{ number_format((float) $inv->grand_total, 2) }}</td>
                    <td>
                        @php
                            $statusBorder = match ($inv->status) {
                                'issued' => 'border-success',
                                'cancelled' => 'border-secondary',
                                default => 'border-warning',
                            };
                        @endphp
                        <select class="form-select form-select-sm tax-invoice-status-select {{ $statusBorder }}"
                                data-id="{{ $inv->id }}"
                                data-current="{{ $inv->status }}"
                                data-url="{{ route('admin.tax-invoices.update-status', $inv) }}"
                                aria-label="Invoice status">
                            @foreach(['draft' => 'Draft', 'issued' => 'Issued', 'cancelled' => 'Cancelled'] as $val => $label)
                                <option value="{{ $val }}" @selected($inv->status === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="text-end text-nowrap">
                        <button type="button" class="btn btn-sm btn-outline-secondary tax-invoice-view-btn"
                                data-id="{{ $inv->id }}"
                                data-editable="{{ $inv->isEditable() ? '1' : '0' }}"
                                title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                            @if($inv->isEditable())
                                <button type="button" class="btn btn-sm btn-outline-warning tax-invoice-edit-btn" data-id="{{ $inv->id }}" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            @else
                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Only draft invoices can be edited">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No tax invoices found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($invoices->hasPages())
    <div class="card-footer" id="taxInvoicesPagination">{!! $invoices->links() !!}</div>
@endif
