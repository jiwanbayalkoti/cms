<!DOCTYPE html>
<html lang="ne">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $taxInvoice->invoice_number }}</title>
    <link rel="stylesheet" href="{{ asset('css/admin-tax-invoice-print.css') }}?v=1">
</head>
<body class="embed-preview">
    @include('admin.tax_invoices.partials.print-bill-sheet', compact('taxInvoice', 'company'))
</body>
</html>
