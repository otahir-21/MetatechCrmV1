<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetToken extends Model
{
    protected $table = 'password_reset_tokens';

    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'email';

    protected $fillable = [
        'email',
        'token',
        'user_id',
        'used',
        'used_at',
        'created_at',
        'expires_at',
        'ip_address',
    ];

    protected $casts = [
        'used' => 'boolean',
        'created_at' => 'datetime',
        'used_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if token is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    /**
     * Check if token is used.
     */
    public function isUsed(): bool
    {
        return $this->used === true;
    }

    /**
     * Check if token is valid (not used and not expired).
     */
    public function isValid(): bool
    {
        return !$this->isUsed() && !$this->isExpired();
    }

    /**
     * Mark token as used.
     */
    public function markAsUsed(): void
    {
        $this->used = true;
        $this->used_at = now();
        $this->save();
    }
}
