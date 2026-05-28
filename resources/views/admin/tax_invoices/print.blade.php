<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice {{ $taxInvoice->invoice_number }} — {{ $company->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/admin-tax-invoice-print.css') }}?v=1">
    <style>
        body { margin: 0; padding: 16px; font-family: Arial, 'Segoe UI', 'Noto Sans Devanagari', sans-serif; font-size: 12px; color: #111; background: #eee; }
        .no-print { text-align: center; margin-bottom: 16px; }
        .no-print button, .no-print a { margin: 0 6px; padding: 8px 16px; cursor: pointer; text-decoration: none; border-radius: 6px; border: 1px solid #ccc; background: #fff; }
        .no-print .btn-primary { background: #2563eb; color: #fff; border-color: #2563eb; }
        @media print { .no-print { display: none !important; } body { padding: 0; background: #fff; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button type="button" class="btn-primary" onclick="window.print()">Print / PDF</button>
        <a href="{{ route('admin.tax-invoices.index', ['view' => $taxInvoice->id]) }}">Back</a>
    </div>
    @include('admin.tax_invoices.partials.print-bill-sheet', compact('taxInvoice', 'company'))
</body>
</html>
