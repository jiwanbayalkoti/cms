<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vehicle Alerts</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <h2>Vehicle service &amp; document alerts</h2>
    <p>Hello {{ $company->name }},</p>
    <p>These vehicles need attention (overdue or due within 30 days):</p>
    <table cellpadding="8" cellspacing="0" border="1" style="border-collapse: collapse; width: 100%;">
        <thead>
            <tr style="background: #f3f4f6;">
                <th align="left">Vehicle</th>
                <th align="left">Item</th>
                <th align="left">Date</th>
                <th align="left">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($alerts as $alert)
                <tr>
                    <td>{{ $alert['vehicle_number'] }}</td>
                    <td>{{ $alert['label'] }}</td>
                    <td>{{ $alert['date_label'] }}</td>
                    <td>{{ $alert['message'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <p>Please review them in Vehicle Management &gt; Documents / Expiry.</p>
    <p>Thank you,<br>{{ config('app.name') }}</p>
</body>
</html>
