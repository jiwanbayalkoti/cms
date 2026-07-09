<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment History</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111827; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .muted { color: #6b7280; }
        .summary { margin: 14px 0; padding: 10px; border: 1px solid #d1d5db; background: #f9fafb; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 7px; text-align: left; vertical-align: top; }
        th { background: #111827; color: #ffffff; }
        .status { text-transform: capitalize; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Payment History</h1>
    <div class="muted">Generated at {{ $generatedAt->format('Y-m-d H:i') }}</div>

    <div class="summary">
        <strong>Company:</strong> {{ $company->name }}<br>
        @if($company->email)
            <strong>Email:</strong> {{ $company->email }}<br>
        @endif
        @if($company->paid_until)
            <strong>Paid Until:</strong> {{ $company->paid_until->format('Y-m-d') }}<br>
        @endif
        <strong>Status:</strong> {{ ucfirst($company->subscription_status ?? 'unknown') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Method</th>
                <th>Amount</th>
                <th>Days</th>
                <th>Reference</th>
                <th>Status</th>
                <th>Expires</th>
            </tr>
        </thead>
        <tbody>
            @forelse($paymentRequests as $request)
                <tr>
                    <td>{{ optional($request->payment_date)->format('Y-m-d') ?: $request->created_at->format('Y-m-d') }}</td>
                    <td>{{ $request->payment_method ?: '-' }}</td>
                    <td>{{ $request->amount ? 'Rs. ' . number_format((float) $request->amount, 2) : '-' }}</td>
                    <td>{{ $request->paid_for_days }}</td>
                    <td>{{ $request->transaction_reference ?: '-' }}</td>
                    <td class="status">{{ $request->status }}</td>
                    <td>{{ optional($request->expires_at)->format('Y-m-d') ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No payment history found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
