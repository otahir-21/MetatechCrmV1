<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Invitation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h1 style="color: #4F46E5; margin-top: 0;">You're Invited!</h1>
    </div>

    <p>Hello{{ $invitation->first_name ? ' ' . $invitation->first_name : '' }},</p>

    <p>You've been invited to join <strong>Metatech Internal CRM</strong> as an employee.</p>

    @if($invitation->department || $invitation->designation)
    <div style="background-color: #f0f9ff; border-left: 4px solid #0ea5e9; padding: 12px; margin: 20px 0;">
        <p style="margin: 0; font-size: 14px;">
            @if($invitation->department)
                <strong>Department:</strong> {{ $invitation->department }}<br>
            @endif
            @if($invitation->designation)
                <strong>Designation:</strong> {{ $invitation->designation }}<br>
            @endif
            @if($invitation->role)
                <strong>Role:</strong> {{ ucfirst($invitation->role) }}
            @endif
        </p>
    </div>
    @endif

    <p>Click the button below to activate your account and set your password:</p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $invitationUrl }}" 
           style="display: inline-block; padding: 12px 24px; background-color: #4F46E5; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">
            Accept Invitation & Activate Account
        </a>
    </div>

    <p>Or copy and paste this link into your browser:</p>
    <p style="word-break: break-all; color: #4F46E5;">{{ $invitationUrl }}</p>

    <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 12px; margin: 20px 0;">
        <p style="margin: 0; font-size: 14px;">
            <strong>Important:</strong>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>This invitation link expires on {{ $invitation->expires_at->format('F j, Y g:i A') }}</li>
                <li>You'll need to set a secure password to activate your account</li>
                <li>After activation, you'll have access to the Internal CRM at <strong>crm.metatech.ae</strong></li>
            </ul>
        </p>
    </div>

    <p>If you're having trouble clicking the button, copy and paste the URL above into your web browser.</p>

    <p style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">
        If you did not expect this invitation, please ignore this email or contact your administrator.
    </p>

    <p style="color: #6b7280; font-size: 14px; margin-top: 20px;">
        Best regards,<br>
        Metatech Internal CRM Team
    </p>
</body>
</html>

