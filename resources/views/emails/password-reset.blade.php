<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h1 style="color: #4F46E5; margin-top: 0;">Password Reset Request</h1>
    </div>

    <p>Hello {{ $user->first_name ?? $user->name }},</p>

    <p>You recently requested to reset your password for your Metatech CRM account. Click the button below to reset it:</p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $resetUrl }}" 
           style="display: inline-block; padding: 12px 24px; background-color: #4F46E5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">
            Reset Password
        </a>
    </div>

    <p>Or copy and paste this link into your browser:</p>
    <p style="word-break: break-all; color: #4F46E5;">{{ $resetUrl }}</p>

    <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; margin: 20px 0;">
        <p style="margin: 0; font-size: 14px;">
            <strong>Important:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>This link will expire in 60 minutes</li>
                <li>This link can only be used once</li>
                <li>If you didn't request this, please ignore this email</li>
            </ul>
        </p>
    </div>

    <p>If you're having trouble clicking the button, copy and paste the URL above into your web browser.</p>

    <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">
        If you did not request a password reset, please ignore this email or contact support if you have concerns.
    </p>

    <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
        Best regards,<br>
        Metatech CRM Team
    </p>
</body>
</html>

