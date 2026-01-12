<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'name',
        'contact_person',
        'email',
        'phone',
        'address',
        'industry',
        'website',
        'notes',
        'status',
        'created_by',
    ];

    /**
     * Get the user who created this client.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all deals for this client.
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Get active deals for this client.
     */
    public function activeDeals(): HasMany
    {
        return $this->hasMany(Deal::class)->whereNotIn('stage', ['won', 'lost']);
    }

    /**
     * Get won deals for this client.
     */
    public function wonDeals(): HasMany
    {
        return $this->hasMany(Deal::class)->where('stage', 'won');
    }

    /**
     * Get total value of won deals.
     */
    public function getTotalWonValueAttribute(): float
    {
        return $this->wonDeals()->sum('value');
    }

    /**
     * Get total value of active deals (pipeline value).
     */
    public function getPipelineValueAttribute(): float
    {
        return $this->activeDeals()->sum('value');
    }
}
