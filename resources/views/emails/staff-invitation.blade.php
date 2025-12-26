<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Invitation</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fa; padding: 20px; border-radius: 5px; margin-bottom: 20px;">
        <h1 style="color: #4F46E5; margin-top: 0;">You're Invited!</h1>
        <p>You have been invited to join <strong>{{ $companyName }}</strong> as a staff member.</p>
    </div>

    <div style="background-color: #ffffff; padding: 20px; border: 1px solid #e5e7eb; border-radius: 5px; margin-bottom: 20px;">
        <p><strong>Invited by:</strong> {{ $inviterName }}</p>
        <p><strong>Role:</strong> {{ ucfirst($invitation->role) }}</p>
        <p><strong>Expires:</strong> {{ $invitation->expires_at->format('F j, Y g:i A') }}</p>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ $acceptUrl }}" 
           style="display: inline-block; background-color: #4F46E5; color: #ffffff; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;">
            Accept Invitation
        </a>
    </div>

    <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; font-size: 12px; color: #6b7280;">
        <p style="margin: 0;">If the button doesn't work, copy and paste this link into your browser:</p>
        <p style="margin: 5px 0 0 0; word-break: break-all;"><a href="{{ $acceptUrl }}">{{ $acceptUrl }}</a></p>
    </div>

    <p style="margin-top: 30px; font-size: 12px; color: #6b7280;">
        This invitation will expire on {{ $invitation->expires_at->format('F j, Y') }}. If you did not expect this invitation, you can safely ignore this email.
    </p>
</body>
</html>

