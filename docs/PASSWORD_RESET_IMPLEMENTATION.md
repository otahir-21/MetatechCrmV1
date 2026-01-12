# Password Reset Implementation

## Overview
Secure password reset functionality with **expiring, single-use tokens** has been implemented for all users **except Product Owners**.

## Features

### ✅ Security Features
1. **Single-Use Tokens**: Each reset token can only be used once
2. **Token Expiration**: Tokens expire after 60 minutes
3. **Secure Token Generation**: 64-character random tokens hashed with SHA256
4. **Product Owner Protection**: Product Owners cannot use password reset (must contact support)

### ✅ User Stories Implemented
- **As a user, I want a secure password reset flow so I can regain access without support tickets.**
- **As a Super Admin, I want reset links to expire and be single-use so accounts stay safe.**

## Database Schema

### `password_reset_tokens` Table
- `email` (primary key): User's email address
- `token` (hashed): SHA256 hash of the reset token
- `user_id`: Foreign key to users table (nullable)
- `used` (boolean): Whether the token has been used
- `used_at` (timestamp): When the token was used
- `created_at` (timestamp): When the token was created
- `expires_at` (timestamp): When the token expires (60 minutes from creation)
- `ip_address`: IP address of the request

## Routes

### Public Routes (No Authentication Required)
- `GET /password/forgot` - Show password reset request form
- `POST /password/email` - Submit password reset request
- `GET /password/reset` - Show password reset form (requires email & token query params)
- `POST /password/reset` - Submit new password

## Components

### Models
- **PasswordResetToken**: Model for password reset tokens with helper methods:
  - `isExpired()`: Check if token is expired
  - `isUsed()`: Check if token has been used
  - `isValid()`: Check if token is valid (not used and not expired)
  - `markAsUsed()`: Mark token as used (single-use enforcement)

### Services
- **PasswordResetService**: Handles all password reset logic:
  - `createResetToken()`: Creates a new reset token
  - `verifyToken()`: Verifies token validity
  - `resetPassword()`: Resets user password and marks token as used
  - `getResetUrl()`: Generates reset URL with token

### Controllers
- **PasswordResetController**: Handles HTTP requests for password reset:
  - `showRequestForm()`: Display forgot password form
  - `requestReset()`: Process password reset request and send email
  - `showResetForm()`: Display password reset form
  - `resetPassword()`: Process password reset

### Email
- **PasswordResetMail**: Mailable class that sends password reset emails with:
  - Reset link with token
  - Expiration notice (60 minutes)
  - Single-use notice
  - Security warnings

### Views
- `auth/password/request.blade.php`: Forgot password form
- `auth/password/reset.blade.php`: Password reset form
- `emails/password-reset.blade.php`: Email template

## Security Measures

1. **Token Hashing**: Tokens are hashed using SHA256 before storage
2. **Email Verification**: Tokens are verified against hashed values
3. **Expiration Check**: Tokens automatically expire after 60 minutes
4. **Single-Use Enforcement**: Tokens are marked as used after password reset
5. **Product Owner Restriction**: Product Owners are explicitly blocked from password reset
6. **IP Tracking**: IP addresses are logged for audit purposes

## Usage

### For Users

1. Click "Forgot your password?" link on login page
2. Enter email address
3. Receive email with reset link
4. Click link (valid for 60 minutes, single-use only)
5. Enter new password
6. Login with new password

### For Product Owners

Password reset is **not available**. Product Owners must contact support to reset their password.

## Testing

### Manual Testing Steps

1. **Test Password Reset Request**:
   ```
   GET http://localhost:8000/password/forgot
   POST http://localhost:8000/password/email
   Body: { "email": "user@example.com" }
   ```

2. **Test Password Reset (with valid token)**:
   ```
   GET http://localhost:8000/password/reset?email=user@example.com&token=<token>
   POST http://localhost:8000/password/reset
   Body: {
     "email": "user@example.com",
     "token": "<token>",
     "password": "NewPassword123!",
     "password_confirmation": "NewPassword123!"
   }
   ```

3. **Test Token Expiration**:
   - Wait 60+ minutes after token creation
   - Try to use expired token (should fail)

4. **Test Single-Use Token**:
   - Use a token to reset password
   - Try to use the same token again (should fail)

5. **Test Product Owner Block**:
   - Try to request password reset for Product Owner email
   - Should receive error message

## Notes

- Password requirements: Minimum 8 characters, must include uppercase, lowercase, number, and special character
- Tokens expire after 60 minutes (configurable in `PasswordResetService::$expirationMinutes`)
- All tokens are single-use only
- Product Owners cannot use password reset functionality

