@extends('admin.layout')

@section('title', 'VAT Bill Design Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">VAT Bill Design — {{ $company->name }}</h1>
        <p class="text-muted mb-0">Company-wise print template and colors</p>
    </div>
    <a href="{{ route('admin.tax-invoices.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i>Back to invoices
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row">
    <div class="col-lg-6">
        <div class="card"><div class="card-body">
            <form method="POST" action="{{ route('admin.tax-invoices.settings') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Default template</label>
                    <select name="vat_bill_template" class="form-select">
                        @foreach(\App\Models\TaxInvoice::templateOptions() as $key => $label)
                            <option value="{{ $key }}" @selected(old('vat_bill_template', $company->vat_bill_template ?? 'nepali_annex5') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Nepali = कर विजक (pink). Modern = Energetic-style: &lt;&lt; TAX INVOICE &gt;&gt;, logo, two-column buyer/invoice info, Indian number format.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Paper accent color</label>
                    <input type="color" name="vat_bill_accent_color" class="form-control form-control-color w-100"
                           value="{{ old('vat_bill_accent_color', $company->vat_bill_accent_color ?? '#f8d7da') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Default VAT %</label>
                    <input type="number" step="0.01" name="default_vat_percent" class="form-control"
                           value="{{ old('default_vat_percent', $company->default_vat_percent ?? 13) }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Footer text (optional)</label>
                    <input type="text" name="vat_bill_footer_text" class="form-control" placeholder="E. & O. E."
                           value="{{ old('vat_bill_footer_text', $company->vat_bill_footer_text ?? '') }}">
                </div>
                <hr>
                <p class="small text-muted mb-2">Seller details come from company profile:</p>
                <ul class="small">
                    <li><strong>Name:</strong> {{ $company->name }}</li>
                    <li><strong>Address:</strong> {{ $company->address ?: '— set in Companies' }}</li>
                    <li><strong>PAN/VAT:</strong> {{ $company->tax_number ?: '— set tax_number' }}</li>
                </ul>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-circle me-1"></i>Save settings
                </button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-body">
            <h6>Template preview</h6>
            <p class="small text-muted">After saving, create a tax invoice and use Print to see full layout.</p>
            <div style="background: {{ $company->vat_bill_accent_color ?? '#f8d7da' }}; padding: 12px; border-radius: 8px;">
                <div style="background:#fff; border:2px solid #333; padding:12px; text-align:center;">
                    <div style="font-size:18px;font-weight:bold;">कर विजक / Tax Invoice</div>
                    <div style="font-size:12px;margin-top:8px;">{{ $company->name }}</div>
                </div>
            </div>
        </div></div>
    </div>
</div>
@endsection
