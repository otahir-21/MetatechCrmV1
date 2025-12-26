# Email/SMTP Setup Guide

## Current Status
Your Laravel application is currently configured to use the `log` mail driver, which writes emails to log files instead of sending them. You need to configure SMTP to actually send invitation emails.

## Option 1: Configure SMTP (Recommended for Production)

Add these settings to your `.env` file:

### For Gmail:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Note for Gmail:** You'll need to use an "App Password" instead of your regular password:
1. Go to your Google Account settings
2. Enable 2-Step Verification
3. Generate an App Password: https://myaccount.google.com/apppasswords

### For Outlook/Office 365:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_USERNAME=your-email@outlook.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@outlook.com
MAIL_FROM_NAME="${APP_NAME}"
```

### For Custom SMTP Server:
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.yourdomain.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

### For mail.metatech.ae (if you have a custom SMTP server):
```env
MAIL_MAILER=smtp
MAIL_HOST=mail.metatech.ae
MAIL_PORT=587
MAIL_USERNAME=noreply@metatech.ae
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@metatech.ae
MAIL_FROM_NAME="Metatech CRM"
```

## Option 2: Check Logs (For Testing)

If you want to see the email content without setting up SMTP, emails are currently being logged. Check:

```bash
tail -f storage/logs/laravel.log
```

Or search for invitation emails:
```bash
grep -A 50 "employee-invitation" storage/logs/laravel.log
```

## Option 3: Use Mailtrap (For Development/Testing)

Mailtrap is great for testing emails without actually sending them:

1. Sign up at https://mailtrap.io (free tier available)
2. Get your SMTP credentials
3. Update `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@metatech.ae
MAIL_FROM_NAME="Metatech CRM"
```

## After Configuration

1. **Update your `.env` file** with the SMTP settings above
2. **Clear config cache:**
   ```bash
   php artisan config:clear
   ```
3. **Test sending an invitation again**

## Verification

To test if email is working:

```bash
php artisan tinker
```

Then in tinker:
```php
Mail::raw('Test email', function ($message) {
    $message->to('your-email@example.com')->subject('Test');
});
```

## Common Issues

1. **"Connection timeout"**: Check firewall/port blocking
2. **"Authentication failed"**: Verify username/password
3. **Gmail "Less secure app" error**: Use App Password instead
4. **Port 587 blocked**: Try port 465 with `MAIL_ENCRYPTION=ssl`

## Security Note

Never commit your `.env` file to version control. It contains sensitive credentials.

