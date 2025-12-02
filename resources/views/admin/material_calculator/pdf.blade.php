<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 20mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; }
        th { background: #f4f4f4; text-transform: uppercase; font-size: 11px; }
        .text-right { text-align: right; }
        .muted { color: #666; font-size: 11px; }
        .summary-table td { border: none; padding: 3px 0; }
    </style>
</head>
<body>
    <h1>Construction Material Calculator</h1>
    <p class="muted">Generated on {{ $generatedAt->format('d M, Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>Work & Description</th>
                <th>Materials</th>
                <th style="width: 120px;">Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{ $item['sn'] }}</td>
                    <td>
                        <strong>{{ $item['work_type'] }}</strong><br>
                        <span class="muted">{{ $item['description'] }}</span>
                        @if(!empty($item['notes']))
                            <br><em>{{ $item['notes'] }}</em>
                        @endif
                    </td>
                    <td>
                        @foreach($item['materials'] as $key => $value)
                            <div>{{ ucfirst(str_replace('_',' ', $key)) }}: <strong>{{ $value }}</strong></div>
                        @endforeach
                    </td>
                    <td class="text-right">
                        @php($cost = $item['cost']['total'] ?? 0)
                        {{ number_format((float) $cost, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(!empty($summary['totals']))
        <h3 style="margin-top: 18px;">Totals</h3>
        <table class="summary-table">
            @foreach($summary['totals'] as $key => $value)
                <tr>
                    <td style="width: 220px;">{{ ucfirst(str_replace('_',' ', $key)) }}</td>
                    <td>: <strong>{{ $value }}</strong></td>
                </tr>
            @endforeach
        </table>
    @endif
</body>
</html>

