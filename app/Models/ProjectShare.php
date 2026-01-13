<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectShare extends Model
{
    protected $fillable = [
        'project_id',
        'resource_type',
        'resource_id',
        'shared_with_user_id',
        'shared_by_user_id',
        'permission',
        'notes',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Get the project.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the user the resource is shared with.
     */
    public function sharedWithUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_with_user_id');
    }

    /**
     * Get the user who shared the resource.
     */
    public function sharedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by_user_id');
    }

    /**
     * Check if the share has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Scope to get active (non-expired) shares.
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope to filter by project.
     */
    public function scopeForProject($query, int $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Scope to filter by resource.
     */
    public function scopeForResource($query, string $resourceType, int $resourceId)
    {
        return $query->where('resource_type', $resourceType)
                     ->where('resource_id', $resourceId);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('shared_with_user_id', $userId);
    }

    /**
     * Get the shared resource (polymorphic).
     */
    public function getResourceAttribute()
    {
        switch ($this->resource_type) {
            case 'task':
                return Task::find($this->resource_id);
            case 'comment':
                return TaskComment::find($this->resource_id);
            default:
                return null;
        }
    }
}
