@php
    use App\Support\IndianNumberFormat;

    $sellerPan = $company->tax_number ?? '';
    $invoiceDateAd = $taxInvoice->invoice_date?->format('d/m/Y');
    $invoiceMiti = $taxInvoice->transaction_date_bs ?? '—';
    $transactionDate = $taxInvoice->transaction_date_bs ?? $invoiceDateAd;
    $paymentMethod = $taxInvoice->payment_method ?? 'cash';
    $paymentDisplay = function (string $key, string $label) use ($paymentMethod) {
        $active = $paymentMethod === $key;

        return $active ? '<strong>' . e($label) . '</strong>' : e($label);
    };
    $totalQty = $taxInvoice->items->sum(fn ($i) => (float) $i->quantity);
    $primaryUnit = $taxInvoice->items->count() === 1
        ? ($taxInvoice->items->first()->unit ?: '—')
        : ($taxInvoice->items->pluck('unit')->filter()->unique()->count() === 1
            ? $taxInvoice->items->first()->unit
            : '—');
    $preparedBy = $preparedByName ?? ($taxInvoice->relationLoaded('creator') ? ($taxInvoice->creator->name ?? '—') : '—');
    $printedAt = now()->format('n/j/Y h:i A');
@endphp
<div class="vat-bill vat-bill-modern">
    <div class="vat-modern-top-title">&lt;&lt; TAX INVOICE &gt;&gt;</div>

    <header class="vat-modern-header">
        <div class="vat-modern-logo-wrap">
            @if($companyLogoUrl ?? null)
                <img src="{{ $companyLogoUrl }}" alt="" class="vat-modern-logo" loading="eager" decoding="async">
            @endif
        </div>
        <div class="vat-modern-company vat-modern-company-center">
        <div class="vat-modern-company-name">{{ $company->name }}</div>
        <div class="vat-modern-company-lines">
            @if(trim((string) $company->address) !== '')
                <div>{{ strtoupper($company->address) }}{{ $company->city ? ', ' . strtoupper($company->city) : '' }}{{ $company->country ? ', ' . strtoupper($company->country) : '' }}</div>
            @endif
            @if($sellerPan)
                <div>PAN : {{ $sellerPan }}</div>
            @endif
            @if($company->phone || $company->email)
                <div>
                    @if($company->phone)Tel. : {{ $company->phone }}@endif
                    @if($company->phone && $company->email), @endif
                    @if($company->email)E-mail: {{ $company->email }}@endif
                </div>
            @endif
        </div>
        </div>
        <div class="vat-modern-header-spacer" aria-hidden="true"></div>
    </header>

    <div class="vat-modern-info-grid">
        <div class="vat-modern-info-col">
            <table class="vat-modern-kv">
                <tr><td class="k">Name</td><td class="v">{{ $taxInvoice->buyer_name }}</td></tr>
                <tr><td class="k">Address</td><td class="v">{{ $taxInvoice->buyer_address }}</td></tr>
                <tr><td class="k">VAT No.</td><td class="v">{{ $taxInvoice->buyer_pan ?: '—' }}</td></tr>
                <tr><td class="k">Contact No.</td><td class="v">{{ $taxInvoice->buyer_phone ?: '—' }}</td></tr>
                <tr><td class="k">Payment System</td><td class="v">{!! $paymentDisplay('cash', 'Cash') !!}/{!! $paymentDisplay('credit', 'Credit') !!}/{!! $paymentDisplay('cheque', 'Cheque') !!}</td></tr>
            </table>
        </div>
        <div class="vat-modern-info-col">
            <table class="vat-modern-kv">
                <tr><td class="k">Invoice No.</td><td class="v fw-bold">{{ $taxInvoice->invoice_number }}</td></tr>
                <tr><td class="k">Invoice Date</td><td class="v">{{ $invoiceDateAd }}</td></tr>
                <tr><td class="k">Invoice Mitti</td><td class="v">{{ $invoiceMiti }}</td></tr>
                <tr><td class="k">Transaction Date</td><td class="v">{{ $transactionDate }}</td></tr>
            </table>
        </div>
    </div>

    <table class="vat-modern-items">
        <thead>
            <tr>
                <th class="sn">S.N.</th>
                <th class="particulars">Particulars</th>
                <th class="qty">Qty.</th>
                <th class="unit">Unit</th>
                <th class="rate">Rate</th>
                <th class="amt">Amount(Rs.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($taxInvoice->items as $item)
                <tr>
                    <td class="text-center">{{ $item->line_number }}.</td>
                    <td class="particulars">{{ $item->description }}</td>
                    <td class="text-end">{{ number_format((float) $item->quantity, 2) }}</td>
                    <td class="text-center">{{ $item->unit ?: '—' }}</td>
                    <td class="text-end">{{ IndianNumberFormat::format((float) $item->unit_price) }}</td>
                    <td class="text-end">{{ IndianNumberFormat::format((float) $item->line_amount) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="vat-modern-items-foot">
                <td></td>
                <td class="fw-bold">Grand Total</td>
                <td class="text-end fw-bold">{{ number_format($totalQty, 2) }}</td>
                <td class="text-center fw-bold">{{ $primaryUnit }}</td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="vat-modern-summary">
        <div class="vat-modern-summary-left">
            <div class="vat-modern-words">
                <strong>Amount In Words :</strong><br>
                {{ $taxInvoice->amount_in_words }}
            </div>
        </div>
        <div class="vat-modern-summary-right">
            <table class="vat-modern-totals">
                <tr><td>Total Amount</td><td>{{ IndianNumberFormat::format((float) $taxInvoice->subtotal) }}</td></tr>
                <tr><td>Discount</td><td>{{ IndianNumberFormat::format((float) $taxInvoice->discount_amount) }}</td></tr>
                <tr><td>Taxable Amount</td><td>{{ IndianNumberFormat::format((float) $taxInvoice->taxable_amount) }}</td></tr>
                <tr><td>VAT {{ number_format((float) $taxInvoice->vat_percent, 0) }}%</td><td>{{ IndianNumberFormat::format((float) $taxInvoice->vat_amount) }}</td></tr>
                <tr class="grand-row"><td><strong>Grand Total</strong></td><td><strong>{{ IndianNumberFormat::format((float) $taxInvoice->grand_total) }}</strong></td></tr>
            </table>
        </div>
    </div>

    @if($taxInvoice->notes || $taxInvoice->reference_number || $taxInvoice->project)
        <div class="vat-modern-note">
            @if($taxInvoice->reference_number)
                <div><strong>Ref:</strong> {{ $taxInvoice->reference_number }}</div>
            @endif
            @if($taxInvoice->project)
                <div><strong>Project:</strong> {{ $taxInvoice->project->name }}</div>
            @endif
            @if($taxInvoice->notes)
                <div>{{ $taxInvoice->notes }}</div>
            @endif
        </div>
    @endif

    <div class="vat-modern-signatures">
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">Received By</div>
        </div>
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-label">Prepared By<br><span class="sig-name">{{ $preparedBy }}</span></div>
        </div>
        <div class="sig-box">
            <div class="sig-line sig-line-tall"></div>
            <div class="sig-label">Authorized Signature</div>
        </div>
    </div>

    <div class="vat-modern-print-bar">
        <span><strong>Printed By :</strong> {{ strtoupper(auth()->user()->name ?? 'ADMIN') }}</span>
        <span><strong>Date &amp; Time :</strong> {{ $printedAt }}</span>
    </div>
</div>
