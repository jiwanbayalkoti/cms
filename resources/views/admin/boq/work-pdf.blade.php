<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Work & BoQ</title>
    <style>
        @page { margin: 18mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #222; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        .muted { color: #666; font-size: 9px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        th, td { border: 1px solid #333; padding: 4px 6px; }
        th { background: #e9ecef; font-size: 9px; }
        .text-end { text-align: right; }
        .work-name { font-weight: bold; margin-bottom: 4px; }
        .subwork-name { font-weight: bold; margin-top: 8px; margin-bottom: 4px; padding-left: 8px; }
        .total-row { font-weight: bold; background: #f8f9fa; }
    </style>
</head>
<body>
    @include('admin.partials.pdf-letterhead', ['company' => $company ?? null])
    <h1>Work & BoQ</h1>
    @if($company)
        <p class="muted">{{ $company->name ?? '' }} @if($company->client) | Client: {{ $company->client }} @endif @if($company->project) | Project: {{ $company->project }} @endif</p>
    @endif
    <p class="muted">Generated on {{ now()->format('d M, Y H:i') }}</p>

    @foreach($works as $work)
        <div class="work-name">{{ $work->name }}</div>
        @if($work->items->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th style="width: 28px;">SN</th>
                        <th>Item Description</th>
                        <th style="width: 50px;">Unit</th>
                        <th class="text-end" style="width: 60px;">Qty</th>
                        <th class="text-end" style="width: 60px;">Rate</th>
                        <th>Rate in Words</th>
                        <th class="text-end" style="width: 70px;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($work->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->item_description ?: '–' }}</td>
                            <td>{{ $item->unit ?: '–' }}</td>
                            <td class="text-end">{{ number_format($item->qty, 4) }}</td>
                            <td class="text-end">{{ number_format($item->rate, 4) }}</td>
                            <td>{{ $item->rate_in_words ?: '–' }}</td>
                            <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td colspan="6" class="text-end">Total:</td>
                        <td class="text-end">{{ number_format($work->items->sum('amount'), 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
        @foreach($work->children ?? [] as $sub)
            <div class="subwork-name">└ {{ $sub->name }}</div>
            @if($sub->items->isNotEmpty())
                <table>
                    <thead>
                        <tr>
                            <th style="width: 28px;">SN</th>
                            <th>Item Description</th>
                            <th style="width: 50px;">Unit</th>
                            <th class="text-end" style="width: 60px;">Qty</th>
                            <th class="text-end" style="width: 60px;">Rate</th>
                            <th>Rate in Words</th>
                            <th class="text-end" style="width: 70px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sub->items as $item)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $item->item_description ?: '–' }}</td>
                                <td>{{ $item->unit ?: '–' }}</td>
                                <td class="text-end">{{ number_format($item->qty, 4) }}</td>
                                <td class="text-end">{{ number_format($item->rate, 4) }}</td>
                                <td>{{ $item->rate_in_words ?: '–' }}</td>
                                <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @endforeach
                        <tr class="total-row">
                            <td colspan="6" class="text-end">Total:</td>
                            <td class="text-end">{{ number_format($sub->items->sum('amount'), 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            @endif
        @endforeach
    @endforeach
</body>
</html>
