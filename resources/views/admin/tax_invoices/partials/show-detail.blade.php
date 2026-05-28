<div class="tax-invoice-view-detail" data-editable="{{ $taxInvoice->isEditable() ? '1' : '0' }}" data-invoice-id="{{ $taxInvoice->id }}" data-status="{{ $taxInvoice->status }}">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <div class="h5 mb-1">#{{ $taxInvoice->invoice_number }}</div>
            <div class="text-muted small">
                {{ $taxInvoice->invoice_date->format('M d, Y') }}
                @if($taxInvoice->transaction_date_bs)
                    · BS: {{ $taxInvoice->transaction_date_bs }}
                @endif
                ·
                <span class="badge bg-{{ $taxInvoice->status === 'issued' ? 'success' : ($taxInvoice->status === 'cancelled' ? 'secondary' : 'warning') }}">
                    {{ ucfirst($taxInvoice->status) }}
                </span>
            </div>
        </div>
        <div class="text-end">
            <div class="fw-bold fs-5">Rs. {{ number_format((float) $taxInvoice->grand_total, 2) }}</div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="card h-100 border"><div class="card-body py-2">
                <h6 class="text-muted small mb-2">Seller (Company)</h6>
                <p class="mb-1 fw-bold">{{ $company->name }}</p>
                <p class="mb-1 small">{{ $company->address ?: '—' }}</p>
                <p class="mb-0 small">PAN: {{ $company->tax_number ?: '—' }}</p>
            </div></div>
        </div>
        <div class="col-md-6">
            <div class="card h-100 border"><div class="card-body py-2">
                <h6 class="text-muted small mb-2">Buyer</h6>
                <p class="mb-1 fw-bold">{{ $taxInvoice->buyer_name }}</p>
                <p class="mb-1 small">{{ $taxInvoice->buyer_address ?: '—' }}</p>
                <p class="mb-0 small">PAN: {{ $taxInvoice->buyer_pan ?: '—' }}</p>
                @if($taxInvoice->buyer_phone)
                    <p class="mb-0 small">Contact: {{ $taxInvoice->buyer_phone }}</p>
                @endif
            </div></div>
        </div>
    </div>

    <div class="table-responsive mb-3">
        <table class="table table-sm table-bordered mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th class="text-end">Qty</th>
                    <th>Unit</th>
                    <th class="text-end">Rate</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($taxInvoice->items as $item)
                    <tr>
                        <td>{{ $item->line_number }}</td>
                        <td>{{ $item->description }}</td>
                        <td class="text-end">{{ number_format((float) $item->quantity, 2) }}</td>
                        <td>{{ $item->unit ?: '—' }}</td>
                        <td class="text-end">{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="text-end">{{ number_format((float) $item->line_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr><td colspan="5" class="text-end">Subtotal</td><td class="text-end">{{ number_format((float) $taxInvoice->subtotal, 2) }}</td></tr>
                @if((float) $taxInvoice->discount_amount > 0)
                    <tr><td colspan="5" class="text-end">Discount</td><td class="text-end">{{ number_format((float) $taxInvoice->discount_amount, 2) }}</td></tr>
                @endif
                <tr><td colspan="5" class="text-end">Taxable</td><td class="text-end">{{ number_format((float) $taxInvoice->taxable_amount, 2) }}</td></tr>
                <tr><td colspan="5" class="text-end">VAT ({{ $taxInvoice->vat_percent }}%)</td><td class="text-end">{{ number_format((float) $taxInvoice->vat_amount, 2) }}</td></tr>
                <tr class="fw-bold"><td colspan="5" class="text-end">Grand Total</td><td class="text-end">{{ number_format((float) $taxInvoice->grand_total, 2) }}</td></tr>
            </tfoot>
        </table>
    </div>

    @if($taxInvoice->amount_in_words)
        <p class="text-muted small mb-3"><strong>In words:</strong> {{ $taxInvoice->amount_in_words }}</p>
    @endif

    @if($taxInvoice->notes || $taxInvoice->reference_number || $taxInvoice->project)
        <div class="small mb-3 p-2 bg-light border rounded">
            @if($taxInvoice->reference_number)<div><strong>Ref:</strong> {{ $taxInvoice->reference_number }}</div>@endif
            @if($taxInvoice->project)<div><strong>Project:</strong> {{ $taxInvoice->project->name }}</div>@endif
            @if($taxInvoice->notes)<div>{{ $taxInvoice->notes }}</div>@endif
        </div>
    @endif

    @if($taxInvoice->isPrintable())
        <div class="tax-invoice-print-preview border rounded bg-white overflow-auto p-2">
            @include('admin.tax_invoices.partials.print-bill-sheet', [
                'taxInvoice' => $taxInvoice,
                'company' => $company,
                'companyLogoUrl' => $companyLogoUrl ?? null,
                'preparedByName' => $preparedByName ?? null,
            ])
        </div>
    @else
        <div class="alert alert-info small mb-0">
            <i class="bi bi-info-circle me-1"></i>
            Print is available after the invoice status is set to <strong>Issued</strong>.
        </div>
    @endif
</div>
