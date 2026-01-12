<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deal extends Model
{
    protected $fillable = [
        'title',
        'client_id',
        'value',
        'currency',
        'stage',
        'priority',
        'assigned_to',
        'expected_close_date',
        'lead_source',
        'notes',
        'lost_reason',
        'won_at',
        'lost_at',
    ];

    protected $casts = [
        'expected_close_date' => 'date',
        'won_at' => 'datetime',
        'lost_at' => 'datetime',
        'value' => 'decimal:2',
    ];

    /**
     * Get the client this deal belongs to.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the user assigned to this deal.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Scope to get deals by stage.
     */
    public function scopeByStage($query, string $stage)
    {
        return $query->where('stage', $stage);
    }

    /**
     * Scope to get deals assigned to a specific user.
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope to get active deals (not won or lost).
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('stage', ['won', 'lost']);
    }

    /**
     * Scope to get won deals.
     */
    public function scopeWon($query)
    {
        return $query->where('stage', 'won');
    }

    /**
     * Scope to get lost deals.
     */
    public function scopeLost($query)
    {
        return $query->where('stage', 'lost');
    }

    /**
     * Mark deal as won.
     */
    public function markAsWon(): void
    {
        $this->update([
            'stage' => 'won',
            'won_at' => now(),
        ]);
    }

    /**
     * Mark deal as lost.
     */
    public function markAsLost(string $reason = null): void
    {
        $this->update([
            'stage' => 'lost',
            'lost_at' => now(),
            'lost_reason' => $reason,
        ]);
    }

    /**
     * Move deal to a specific stage.
     */
    public function moveToStage(string $stage): void
    {
        $this->update(['stage' => $stage]);
        
        // Auto-set won_at or lost_at
        if ($stage === 'won' && !$this->won_at) {
            $this->update(['won_at' => now()]);
        } elseif ($stage === 'lost' && !$this->lost_at) {
            $this->update(['lost_at' => now()]);
        }
    }

    /**
     * Check if deal is active.
     */
    public function isActive(): bool
    {
        return !in_array($this->stage, ['won', 'lost']);
    }

    /**
     * Get formatted value with currency.
     */
    public function getFormattedValueAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->value, 2);
    }

    /**
     * Get stage label for display.
     */
    public function getStageLabelAttribute(): string
    {
        return match($this->stage) {
            'new_lead' => 'New Lead',
            'contacted' => 'Contacted',
            'qualified' => 'Qualified',
            'proposal_sent' => 'Proposal Sent',
            'negotiation' => 'Negotiation',
            'won' => 'Won',
            'lost' => 'Lost',
            default => ucfirst($this->stage),
        };
    }

    /**
     * Get priority badge color.
     */
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'high' => 'red',
            'medium' => 'yellow',
            'low' => 'green',
            default => 'gray',
        };
    }
}
