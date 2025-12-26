<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyOwnerInvitation extends Model
{
    protected $fillable = [
        'email',
        'token',
        'company_name',
        'subdomain',
        'first_name',
        'last_name',
        'invited_by',
        'accepted',
        'accepted_at',
        'expires_at',
        'ip_address',
    ];

    protected $casts = [
        'accepted' => 'boolean',
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * The inviter who sent this invitation (Product Owner).
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Check if the invitation is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the invitation has been accepted.
     */
    public function isAccepted(): bool
    {
        return $this->accepted === true;
    }

    /**
     * Check if the invitation is valid (not accepted and not expired).
     */
    public function isValid(): bool
    {
        return !$this->isAccepted() && !$this->isExpired();
    }

    /**
     * Mark the invitation as accepted.
     */
    public function markAsAccepted(): void
    {
        $this->update([
            'accepted' => true,
            'accepted_at' => now(),
        ]);
    }
}
