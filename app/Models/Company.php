<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'company_name',
        'subdomain',
        'company_super_admin_id',
        'status',
        'status_reason',
        'blocked_at',
        'blocked_by',
        'subscription_details',
    ];

    protected $casts = [
        'blocked_at' => 'datetime',
        'subscription_details' => 'array',
    ];

    /**
     * Get the company super admin user.
     */
    public function companySuperAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'company_super_admin_id');
    }

    /**
     * Get who blocked this company.
     */
    public function blockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    /**
     * Get all users in this company.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'subdomain', 'subdomain');
    }
}
