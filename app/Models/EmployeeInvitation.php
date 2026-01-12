<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeInvitation extends Model
{
    protected $fillable = [
        'email',
        'token',
        'invited_by',
        'role',
        'department',
        'designation',
        'joined_date',
        'first_name',
        'last_name',
        'accepted',
        'accepted_at',
        'expires_at',
        'ip_address',
    ];

    protected $casts = [
        'accepted' => 'boolean',
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
        'joined_date' => 'date',
    ];

    /**
     * Get the user who sent the invitation.
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Check if invitation is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if invitation is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->accepted === true;
    }

    /**
     * Check if invitation is valid (not accepted and not expired).
     */
    public function isValid(): bool
    {
        return !$this->isAccepted() && !$this->isExpired();
    }

    /**
     * Mark invitation as accepted.
     */
    public function markAsAccepted(): void
    {
        $this->accepted = true;
        $this->accepted_at = now();
        $this->save();
    }
}
