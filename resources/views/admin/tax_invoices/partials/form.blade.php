@php
    $invoice = $taxInvoice ?? null;
    $items = old('items', $invoice ? $invoice->items->map(fn ($i) => [
        'hs_code' => $i->hs_code,
        'description' => $i->description,
        'quantity' => $i->quantity,
        'unit' => $i->unit,
        'unit_price' => $i->unit_price,
    ])->toArray() : [['description' => '', 'quantity' => 1, 'unit_price' => '', 'hs_code' => '', 'unit' => '']]);
@endphp

<div class="row g-3 mb-3">
    <div class="col-md-2">
        <label class="form-label">बिजक नं. / Invoice No. <span class="text-danger">*</span></label>
        <input type="text" name="invoice_number" class="form-control" required
               value="{{ old('invoice_number', $invoice?->invoice_number ?? $suggestedNumber) }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Date (AD) <span class="text-danger">*</span></label>
        <input type="date" name="invoice_date" class="form-control" required
               value="{{ old('invoice_date', $invoice?->invoice_date?->format('Y-m-d') ?? date('Y-m-d')) }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">मिति (BS)</label>
        <input type="text" name="transaction_date_bs" class="form-control" placeholder="2083-02-05"
               value="{{ old('transaction_date_bs', $invoice?->transaction_date_bs ?? '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Template</label>
        <select name="template" class="form-select">
            @foreach($templates as $key => $label)
                <option value="{{ $key }}" @selected(old('template', $invoice?->template ?? $company->vat_bill_template ?? 'nepali_annex5') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">VAT %</label>
        <input type="number" step="0.01" name="vat_percent" class="form-control" id="vat_percent"
               value="{{ old('vat_percent', $invoice?->vat_percent ?? $company->default_vat_percent ?? 13) }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            @foreach(['draft' => 'Draft', 'issued' => 'Issued', 'cancelled' => 'Cancelled'] as $k => $v)
                <option value="{{ $k }}" @selected(old('status', $invoice?->status ?? 'draft') === $k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3">
        <label class="form-label">Customer</label>
        <select name="customer_id" id="tax_invoice_customer" class="form-select">
            <option value="">— Manual buyer —</option>
            @foreach($customers as $c)
                <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-address="{{ $c->address }}" data-pan="{{ $c->tax_number }}" data-phone="{{ $c->phone ?? $c->mobile }}"
                    @selected(old('customer_id', $invoice?->customer_id ?? '') == $c->id)>{{ $c->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Buyer name <span class="text-danger">*</span></label>
        <input type="text" name="buyer_name" id="buyer_name" class="form-control" required
               value="{{ old('buyer_name', $invoice?->buyer_name ?? '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Buyer PAN</label>
        <input type="text" name="buyer_pan" id="buyer_pan" class="form-control" maxlength="20"
               value="{{ old('buyer_pan', $invoice?->buyer_pan ?? '') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Contact No.</label>
        <input type="text" name="buyer_phone" id="buyer_phone" class="form-control" maxlength="50"
               value="{{ old('buyer_phone', $invoice?->buyer_phone ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Payment method</label>
        <select name="payment_method" class="form-select">
            @foreach($paymentMethods as $key => $label)
                <option value="{{ $key }}" @selected(old('payment_method', $invoice?->payment_method ?? 'cash') === $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label">Buyer address</label>
        <input type="text" name="buyer_address" id="buyer_address" class="form-control"
               value="{{ old('buyer_address', $invoice?->buyer_address ?? '') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Project</label>
        <select name="project_id" class="form-select">
            <option value="">None</option>
            @foreach($projects as $p)
                <option value="{{ $p->id }}" @selected(old('project_id', $invoice?->project_id ?? '') == $p->id)>{{ $p->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Reference</label>
        <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number', $invoice?->reference_number ?? '') }}">
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Line items (विवरण)</span>
        <button type="button" class="btn btn-sm btn-primary" id="taxInvoiceAddRowBtn">
            <i class="bi bi-plus-lg me-1"></i>Add Item
        </button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0" id="taxItemsTable">
                <thead class="table-light">
                    <tr>
                        <th>H.S. Code</th>
                        <th style="min-width:280px">Description / विवरण</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Rate</th>
                        <th>Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="taxItemsBody">
                    @foreach($items as $idx => $item)
                        <tr class="tax-item-row">
                            <td><input type="text" name="items[{{ $idx }}][hs_code]" class="form-control form-control-sm" value="{{ $item['hs_code'] ?? '' }}"></td>
                            <td><textarea name="items[{{ $idx }}][description]" class="form-control form-control-sm tax-desc-input" rows="4" required>{{ $item['description'] ?? '' }}</textarea></td>
                            <td><input type="number" step="0.0001" min="0.0001" name="items[{{ $idx }}][quantity]" class="form-control form-control-sm qty-input" value="{{ $item['quantity'] ?? 1 }}" required></td>
                            <td><input type="text" name="items[{{ $idx }}][unit]" class="form-control form-control-sm" value="{{ $item['unit'] ?? '' }}"></td>
                            <td><input type="number" step="0.01" min="0" name="items[{{ $idx }}][unit_price]" class="form-control form-control-sm rate-input" value="{{ $item['unit_price'] ?? '' }}" required></td>
                            <td class="line-amt text-end align-middle">0.00</td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger tax-item-remove-btn" title="Remove row"><i class="bi bi-trash"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-2">
        <label class="form-label">Discount %</label>
        <input type="number" step="0.01" min="0" name="discount_percent" id="discount_percent" class="form-control"
               value="{{ old('discount_percent', $invoice?->discount_percent ?? 0) }}">
    </div>
    <div class="col-md-2">
        <label class="form-label">Discount amount</label>
        <input type="number" step="0.01" min="0" name="discount_amount" id="discount_amount" class="form-control"
               value="{{ old('discount_amount', $invoice?->discount_amount ?? 0) }}">
    </div>
    <div class="col-md-8">
        <div class="bg-light border rounded p-3 h-100">
            <div class="row text-end">
                <div class="col-8">Subtotal:</div><div class="col-4" id="preview_subtotal">0.00</div>
                <div class="col-8">Taxable:</div><div class="col-4" id="preview_taxable">0.00</div>
                <div class="col-8">VAT:</div><div class="col-4" id="preview_vat">0.00</div>
                <div class="col-8 fw-bold">Grand Total:</div><div class="col-4 fw-bold" id="preview_grand">0.00</div>
            </div>
        </div>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Notes</label>
    <textarea name="notes" class="form-control" rows="4">{{ old('notes', $invoice?->notes ?? '') }}</textarea>
</div>

<div class="alert alert-info small mb-0">
    <strong>Company on print:</strong> {{ $company->name }} · PAN: {{ $company->tax_number ?: '—' }} · {{ $company->address ?: 'Set address in company settings' }}
    · <a href="{{ route('admin.tax-invoices.settings') }}">VAT bill design settings</a>
</div>
