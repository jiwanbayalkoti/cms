<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Required</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <h2>Payment Required</h2>

    <p>Hello {{ $company->name }},</p>

    <p>Your 7-day trial period has ended. Your company profile has been locked until payment is completed.</p>

    <p>
        <strong>Company:</strong> {{ $company->name }}<br>
        @if($company->trial_started_at)
            <strong>Trial Started:</strong> {{ $company->trial_started_at->format('Y-m-d') }}<br>
        @endif
        @if($company->trial_ends_at)
            <strong>Trial Ended:</strong> {{ $company->trial_ends_at->format('Y-m-d') }}
        @endif
    </p>

    <p>Please contact the super admin to complete payment and unlock your account.</p>

    <p>Thank you,<br>{{ config('app.name') }}</p>
</body>
</html>
