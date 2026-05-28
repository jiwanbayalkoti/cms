@php
    $accent = $company->vat_bill_accent_color ?? '#f8d7da';
    $sellerPan = $company->tax_number ?? '';
    $buyerPan = $taxInvoice->buyer_pan ?? ($taxInvoice->customer->tax_number ?? '');
    $transactionDate = $taxInvoice->transaction_date_bs ?: $taxInvoice->invoice_date?->format('Y-m-d');
    $issueDate = $taxInvoice->transaction_date_bs
        ? $taxInvoice->invoice_date?->format('Y-m-d')
        : ($taxInvoice->invoice_date?->format('Y-m-d'));
    $companyName = $company->name ?? '—';
    $companyAddress = trim((string) ($company->address ?? ''));
    if ($company->city) {
        $companyAddress = trim($companyAddress . ($companyAddress ? ', ' : '') . $company->city);
    }
@endphp
<div class="vat-bill vat-bill-nepali" style="--vat-accent: {{ $accent }};">
    <header class="vat-header-np">
        <div class="vat-title">कर विजक</div>
        <div class="vat-title-sub">(Tax Invoice)</div>

        @if($companyLogoUrl ?? null)
            <img src="{{ $companyLogoUrl }}" alt="" class="vat-header-logo" loading="eager" decoding="async">
        @endif

        <div class="vat-company-name-np">{{ $companyName }}</div>
        @if($companyAddress !== '')
            <div class="vat-company-address-np">{{ $companyAddress }}</div>
        @endif

        <table class="vat-header-meta">
            <tr>
                <td class="vat-header-meta-left">
                    <div class="vat-pan-label">विक्रेताको करदाता दर्ता नं. (PAN)</div>
                    <div>@include('admin.tax_invoices.partials.pan-boxes', ['pan' => $sellerPan])</div>
                </td>
                <td class="vat-header-meta-right">
                    <table class="vat-date-table">
                        <tr>
                            <td class="label">कारोबार भएको मिति:</td>
                            <td class="value">{{ $transactionDate }}</td>
                        </tr>
                        <tr>
                            <td class="label">विजक जारी भएको मिति:</td>
                            <td class="value">{{ $issueDate }}</td>
                        </tr>
                        <tr>
                            <td class="label">बिजक नं.:</td>
                            <td class="value fw-bold">{{ $taxInvoice->invoice_number }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </header>

    <table class="vat-meta-table vat-buyer-table">
        <tr>
            <td class="label">क्रेताको नाम:</td>
            <td class="value" colspan="3">{{ $taxInvoice->buyer_name }}</td>
        </tr>
        <tr>
            <td class="label">ठेगाना:</td>
            <td class="value" colspan="3">{{ $taxInvoice->buyer_address }}</td>
        </tr>
        <tr>
            <td class="label">क्रेताको करदाता दर्ता नं.:</td>
            <td>@include('admin.tax_invoices.partials.pan-boxes', ['pan' => $buyerPan])</td>
            <td class="label">भुक्तानीको तरिका:</td>
            <td class="value">
                @foreach(\App\Models\TaxInvoice::paymentMethodOptions() as $key => $label)
                    <span class="pay-opt">{{ $key === $taxInvoice->payment_method ? '☑' : '☐' }} {{ explode(' / ', $label)[0] }}</span>
                @endforeach
            </td>
        </tr>
    </table>

    <table class="vat-items">
        <thead>
            <tr>
                <th style="width:4%">क्र.सं.</th>
                <th style="width:10%">एच.एस. कोड</th>
                <th>विवरण</th>
                <th style="width:8%">परिमाण</th>
                <th style="width:12%">प्रति इकाई मूल्य</th>
                <th colspan="2" style="width:18%">जम्मा मूल्य रु.</th>
            </tr>
            <tr>
                <th colspan="5"></th>
                <th style="width:9%">रु.</th>
                <th style="width:9%">पै.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($taxInvoice->items as $item)
                @php $parts = $taxInvoice->splitAmount((float) $item->line_amount); @endphp
                <tr>
                    <td class="text-center">{{ $item->line_number }}</td>
                    <td class="text-center">{{ $item->hs_code ?? '—' }}</td>
                    <td class="desc-cell">{{ $item->description }}</td>
                    <td class="text-end">{{ number_format((float) $item->quantity, 2) }}{{ $item->unit ? ' '.$item->unit : '' }}</td>
                    <td class="text-end">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="text-end">{{ number_format($parts['rupees']) }}</td>
                    <td class="text-end">{{ str_pad((string) $parts['paisa'], 2, '0', STR_PAD_LEFT) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            @php
                $sub = $taxInvoice->splitAmount((float) $taxInvoice->subtotal);
                $tax = $taxInvoice->splitAmount((float) $taxInvoice->taxable_amount);
                $vat = $taxInvoice->splitAmount((float) $taxInvoice->vat_amount);
                $grand = $taxInvoice->splitAmount((float) $taxInvoice->grand_total);
            @endphp
            <tr>
                <td colspan="4" rowspan="5" class="words-cell">
                    <strong>अक्षरेपी रु.</strong><br>
                    {{ $taxInvoice->amount_in_words }}
                </td>
                <td class="label-col">जम्मा</td>
                <td class="text-end">{{ number_format($sub['rupees']) }}</td>
                <td class="text-end">{{ str_pad((string) $sub['paisa'], 2, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td class="label-col">प्रतिशतले छुट</td>
                <td class="text-end" colspan="2">{{ $taxInvoice->discount_amount > 0 ? number_format((float) $taxInvoice->discount_amount, 2) : '—' }}</td>
            </tr>
            <tr>
                <td class="label-col">कर लाग्ने मूल्य</td>
                <td class="text-end">{{ number_format($tax['rupees']) }}</td>
                <td class="text-end">{{ str_pad((string) $tax['paisa'], 2, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td class="label-col">{{ number_format((float) $taxInvoice->vat_percent, 0) }} प्रतिशतले कर</td>
                <td class="text-end">{{ number_format($vat['rupees']) }}</td>
                <td class="text-end">{{ str_pad((string) $vat['paisa'], 2, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td class="label-col fw-bold">जम्मा मूल्य</td>
                <td class="text-end fw-bold">{{ number_format($grand['rupees']) }}</td>
                <td class="text-end fw-bold">{{ str_pad((string) $grand['paisa'], 2, '0', STR_PAD_LEFT) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="vat-signatures">
        <div>क्रेताको दस्तखत</div>
        <div>विक्रेताको दस्तखत</div>
    </div>
    @if($company->vat_bill_footer_text)
        <div class="vat-footer-note">{{ $company->vat_bill_footer_text }}</div>
    @endif
</div>
