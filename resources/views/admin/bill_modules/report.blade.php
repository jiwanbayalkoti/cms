<!DOCTYPE html>
<html>
<head>
    <title>Bill Report - {{ $bill_module->title }}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .items-table th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .summary { margin-top: 20px; }
        .summary-table { width: 100%; }
        .summary-table td { padding: 5px; }
        .grand-total { font-weight: bold; font-size: 1.1em; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" class="btn btn-primary">Print</button>
        <a href="{{ route('admin.bill-modules.show', $bill_module) }}" class="btn btn-secondary">Back</a>
    </div>

    <div class="header">
        <h1>CONSTRUCTION FINAL BILL / ESTIMATE</h1>
        <h2>{{ $bill_module->title }}</h2>
    </div>

    <table class="info-table">
        <tr>
            <td><strong>Project:</strong> {{ $bill_module->project->name ?? '—' }}</td>
            <td><strong>MB Number:</strong> {{ $bill_module->mb_number ?? '—' }}</td>
        </tr>
        <tr>
            <td><strong>MB Date:</strong> {{ $bill_module->mb_date ? $bill_module->mb_date->format('Y-m-d') : '—' }}</td>
            <td><strong>Version:</strong> {{ $bill_module->version }}</td>
        </tr>
        <tr>
            <td><strong>Status:</strong> {{ ucfirst($bill_module->status) }}</td>
            <td><strong>Date:</strong> {{ $bill_module->created_at->format('Y-m-d') }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>S.N.</th>
                <th>Category</th>
                <th>Description</th>
                <th>Unit</th>
                <th class="text-right">Quantity</th>
                <th class="text-right">Wastage %</th>
                <th class="text-right">Effective Qty</th>
                <th class="text-right">Unit Rate</th>
                <th class="text-right">Amount</th>
                <th class="text-right">Tax %</th>
                <th class="text-right">Net Amount</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bill_module->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->billCategory->name ?? $item->category ?? '—' }}<br><small>{{ $item->billSubcategory->name ?? $item->subcategory ?? '' }}</small></td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->uom }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 3) }}</td>
                    <td class="text-right">{{ number_format($item->wastage_percent, 2) }}%</td>
                    <td class="text-right">{{ number_format($item->effective_quantity, 3) }}</td>
                    <td class="text-right">{{ number_format($item->unit_rate, 2) }}</td>
                    <td class="text-right">{{ number_format($item->total_amount, 2) }}</td>
                    <td class="text-right">{{ number_format($item->tax_percent, 2) }}%</td>
                    <td class="text-right">{{ number_format($item->net_amount, 2) }}</td>
                    <td>{{ $item->remarks }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($bill_module->aggregate)
        <div class="summary">
            <table class="summary-table">
                <tr>
                    <td><strong>Subtotal:</strong></td>
                    <td class="text-right">{{ number_format($bill_module->aggregate->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Tax Total:</strong></td>
                    <td class="text-right">{{ number_format($bill_module->aggregate->tax_total, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Overhead ({{ $bill_module->aggregate->overhead_percent }}%):</strong></td>
                    <td class="text-right">{{ number_format($bill_module->aggregate->overhead_amount, 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Contingency ({{ $bill_module->aggregate->contingency_percent }}%):</strong></td>
                    <td class="text-right">{{ number_format($bill_module->aggregate->contingency_amount, 2) }}</td>
                </tr>
                <tr class="grand-total">
                    <td><strong>GRAND TOTAL:</strong></td>
                    <td class="text-right"><strong>{{ number_format($bill_module->aggregate->grand_total, 2) }}</strong></td>
                </tr>
            </table>
        </div>
    @endif

    <div style="margin-top: 50px; display: flex; justify-content: space-between;">
        <div>
            <p><strong>Prepared By:</strong></p>
            <p>{{ $bill_module->creator->name ?? '—' }}</p>
            <p>_________________</p>
        </div>
        @if($bill_module->approver)
            <div>
                <p><strong>Approved By:</strong></p>
                <p>{{ $bill_module->approver->name }}</p>
                <p>_________________</p>
            </div>
        @endif
    </div>
</body>
</html>

