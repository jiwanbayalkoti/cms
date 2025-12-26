<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Changed</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f9fafb;
            border-radius: 8px;
            padding: 30px;
            border: 1px solid #e5e7eb;
        }
        .header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            margin: -30px -30px 30px -30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .credentials {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .credentials strong {
            color: #d97706;
            display: block;
            margin-bottom: 8px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 12px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .warning {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            padding: 12px;
            margin: 15px 0;
            border-radius: 4px;
            font-size: 14px;
        }
        .info {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
            padding: 12px;
            margin: 15px 0;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Password Changed</h1>
        </div>
        
        <div class="content">
            <p>Hello <strong>{{ $user->name }}</strong>,</p>
            
            @if($changedByAdmin)
            <p>Your account password has been changed by an administrator in the <strong>{{ config('app.name') }}</strong> system.</p>
            @else
            <p>Your account password has been successfully changed in the <strong>{{ config('app.name') }}</strong> system.</p>
            @endif
            
            <div class="credentials">
                <strong>Your Updated Login Credentials:</strong>
                <p><strong>Email:</strong> {{ $user->email }}</p>
                <p><strong>New Password:</strong> {{ $password }}</p>
            </div>
            
            <div class="warning">
                <strong>⚠️ Security Notice:</strong> If you did not request this password change, please contact your administrator immediately and change your password.
            </div>
            
            @if($changedByAdmin)
            <div class="info">
                <strong>ℹ️ Note:</strong> Your password was changed by an administrator. Please log in with your new password and consider changing it to something you prefer.
            </div>
            @endif
            
            <p>You can now log in to your account using your new password:</p>
            
            <div style="text-align: center;">
                <a href="{{ $loginUrl }}" class="button">Login to Your Account</a>
            </div>
            
            <p style="margin-top: 30px;">If you have any questions or need assistance, please contact your administrator.</p>
        </div>
        
        <div class="footer">
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

