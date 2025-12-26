<?php

namespace App\Services;

use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetService
{
    /**
     * Token expiration time in minutes (default: 60 minutes / 1 hour).
     */
    protected int $expirationMinutes = 60;

    /**
     * Create a password reset token for user.
     *
     * @param string $email
     * @param string $ipAddress
     * @return PasswordResetToken
     * @throws \Exception
     */
    public function createResetToken(string $email, string $ipAddress): PasswordResetToken
    {
        $user = User::where('email', strtolower(trim($email)))->first();

        if (!$user) {
            // Don't reveal if email exists (security best practice)
            throw new \Exception('If that email exists, a password reset link has been sent.', 200);
        }

        // Exclude Product Owner from password reset
        if ($user->isProductOwner()) {
            throw new \Exception('Password reset is not available for this account. Please contact support.', 403);
        }

        // Generate secure token (64 characters)
        $token = Str::random(64);
        $expiresAt = now()->addMinutes($this->expirationMinutes);

        // Delete any existing tokens for this email
        PasswordResetToken::where('email', strtolower($email))->delete();

        // Hash token using SHA256 for consistent verification (not bcrypt which is different each time)
        $hashedToken = hash('sha256', $token);

        // Create new token
        $resetToken = PasswordResetToken::create([
            'email' => strtolower(trim($email)),
            'token' => $hashedToken, // Store hashed version
            'user_id' => $user->id,
            'used' => false,
            'created_at' => now(),
            'expires_at' => $expiresAt,
            'ip_address' => $ipAddress,
        ]);

        // Attach plain token for email (not stored in DB)
        $resetToken->plain_token = $token;

        return $resetToken;
    }

    /**
     * Verify reset token.
     *
     * @param string $email
     * @param string $token
     * @return PasswordResetToken|null
     */
    public function verifyToken(string $email, string $token): ?PasswordResetToken
    {
        $resetToken = PasswordResetToken::where('email', strtolower(trim($email)))->first();

        if (!$resetToken) {
            return null;
        }

        // Check if token is used
        if ($resetToken->isUsed()) {
            return null;
        }

        // Check if token is expired
        if ($resetToken->isExpired()) {
            return null;
        }

        // Verify token hash matches (using SHA256)
        $hashedToken = hash('sha256', $token);
        if ($hashedToken !== $resetToken->token) {
            return null;
        }

        return $resetToken;
    }

    /**
     * Reset password using token.
     *
     * @param string $email
     * @param string $token
     * @param string $newPassword
     * @return bool
     * @throws \Exception
     */
    public function resetPassword(string $email, string $token, string $newPassword): bool
    {
        return DB::transaction(function () use ($email, $token, $newPassword) {
            $resetToken = $this->verifyToken($email, $token);

            if (!$resetToken) {
                throw new \Exception('Invalid or expired reset token.', 400);
            }

            $user = User::find($resetToken->user_id);
            if (!$user) {
                throw new \Exception('User not found.', 404);
            }

            // Update password
            $user->password = Hash::make($newPassword);
            $user->save();

            // Mark token as used (single-use)
            $resetToken->markAsUsed();

            // Delete all other tokens for this user (except the one we just used)
            PasswordResetToken::where('email', $email)
                ->where('email', '!=', $resetToken->email) // This will never match, but safe check
                ->delete();
            
            // Actually, since email is primary key, there should only be one token per email
            // So we don't need to delete others - the markAsUsed() is sufficient

            return true;
        });
    }

    /**
     * Get reset URL for token.
     *
     * @param string $email
     * @param string $plainToken
     * @return string
     */
    public function getResetUrl(string $email, string $plainToken): string
    {
        return url('/password/reset?email=' . urlencode($email) . '&token=' . urlencode($plainToken));
    }
}

