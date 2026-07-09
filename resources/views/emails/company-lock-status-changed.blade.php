<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Company {{ $status === 'locked' ? 'Locked' : 'Unlocked' }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <h2>Company {{ $status === 'locked' ? 'Locked' : 'Unlocked' }}</h2>

    <p>Hello {{ $company->name }},</p>

    @if($status === 'locked')
        <p>Your company profile has been manually locked by the super admin. Please contact the super admin to resolve payment or account access.</p>
    @else
        <p>Your company profile has been unlocked by the super admin. You can now access the system again.</p>
    @endif

    <p>
        <strong>Company:</strong> {{ $company->name }}<br>
        <strong>Status:</strong> {{ ucfirst($company->subscription_status ?? $status) }}
    </p>

    <p>Thank you,<br>{{ config('app.name') }}</p>
</body>
</html>
