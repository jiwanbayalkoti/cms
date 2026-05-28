@php
    $isEdit = isset($taxInvoice) && $taxInvoice;
@endphp
<div id="taxInvoiceFormErrors" class="alert alert-danger d-none mb-3"></div>
<form id="taxInvoiceCrudForm" method="POST"
      action="{{ $isEdit ? route('admin.tax-invoices.update', $taxInvoice) : route('admin.tax-invoices.store') }}"
      data-mode="{{ $isEdit ? 'edit' : 'create' }}"
      data-invoice-id="{{ $isEdit ? $taxInvoice->id : '' }}">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif
    @include('admin.tax_invoices.partials.form')
</form>
@if($isEdit)
    <div class="mt-3 pt-3 border-top">
        <button type="button" class="btn btn-outline-danger btn-sm" id="taxInvoiceDeleteBtn">
            <i class="bi bi-trash me-1"></i>Delete invoice
        </button>
    </div>
@endif
