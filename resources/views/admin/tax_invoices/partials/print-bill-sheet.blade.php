@php
    $isModern = ($taxInvoice->template ?? 'nepali_annex5') === 'english_standard';
@endphp
<div class="vat-sheet {{ $isModern ? 'sheet-modern' : '' }}" @if(!$isModern) style="--vat-accent: {{ $company->vat_bill_accent_color ?? '#f8d7da' }};" @endif>
    @if($isModern)
        @include('admin.tax_invoices.partials.print-english', compact('taxInvoice', 'company', 'companyLogoUrl', 'preparedByName'))
    @else
        @include('admin.tax_invoices.partials.print-nepali', compact('taxInvoice', 'company', 'companyLogoUrl', 'preparedByName'))
    @endif
</div>
