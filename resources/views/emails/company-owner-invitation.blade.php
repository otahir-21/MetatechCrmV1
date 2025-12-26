<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Owner Invitation - Metatech CRM</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #4F46E5;
            margin: 0;
            font-size: 24px;
        }
        .content {
            margin-bottom: 30px;
        }
        .content p {
            margin-bottom: 15px;
        }
        .info-box {
            background-color: #F3F4F6;
            border-left: 4px solid #4F46E5;
            padding: 15px;
            margin: 20px 0;
        }
        .info-box strong {
            display: block;
            margin-bottom: 5px;
            color: #1F2937;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #4F46E5;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background-color: #4338CA;
        }
        .link {
            color: #4F46E5;
            word-break: break-all;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            color: #6B7280;
            font-size: 14px;
        }
        .login-url-box {
            background-color: #EFF6FF;
            border: 1px solid #3B82F6;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }
        .login-url-box strong {
            display: block;
            margin-bottom: 8px;
            color: #1E40AF;
        }
        .login-url-box .url {
            font-family: monospace;
            font-size: 14px;
            color: #1E40AF;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Metatech CRM</h1>
        </div>

        <div class="content">
            <p>Hello{{ $invitation->first_name ? ' ' . $invitation->first_name : '' }},</p>

            <p>You have been invited to become the <strong>Company Owner (Super Admin)</strong> for <strong>{{ $invitation->company_name }}</strong> on Metatech CRM.</p>

            <div class="info-box">
                <strong>Company Details:</strong>
                <div>Company Name: {{ $invitation->company_name }}</div>
                <div>Subdomain: {{ $invitation->subdomain }}</div>
            </div>

            <p>To activate your account and set your password, please click the button below:</p>

            <div style="text-align: center;">
                <a href="{{ $invitationUrl }}" class="button">Activate Account & Set Password</a>
            </div>

            <p>Or copy and paste this link into your browser:</p>
            <p><a href="{{ $invitationUrl }}" class="link">{{ $invitationUrl }}</a></p>

            <div class="login-url-box">
                <strong>Your Company Portal Login URL:</strong>
                <div class="url">{{ $loginUrlDisplay }}</div>
                <div style="margin-top: 10px;">
                    <a href="{{ $loginUrl }}" class="link">{{ $loginUrl }}</a>
                </div>
            </div>

            <p><strong>Important:</strong></p>
            <ul>
                <li>This invitation link will expire in <strong>7 days</strong></li>
                <li>After activating your account, you can login using the URL above</li>
                <li>Use the email address: <strong>{{ $invitation->email }}</strong></li>
            </ul>

            <p>If you did not expect this invitation, you can safely ignore this email.</p>
        </div>

        <div class="footer">
            <p>This is an automated message from Metatech CRM. Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} Metatech CRM. All rights reserved.</p>
        </div>
    </div>
</body>
</html>

