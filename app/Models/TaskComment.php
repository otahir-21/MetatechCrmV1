<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'comment',
        'parent_comment_id',
        'mentions',
        'attachments',
        'is_internal_only',
    ];

    protected $casts = [
        'mentions' => 'array',
        'attachments' => 'array',
        'is_internal_only' => 'boolean',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parentComment(): BelongsTo
    {
        return $this->belongsTo(TaskComment::class, 'parent_comment_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(TaskComment::class, 'parent_comment_id')->orderBy('created_at', 'asc');
    }

    /**
     * Scope to get only client-visible comments (not internal-only).
     */
    public function scopeVisibleToClients($query)
    {
        return $query->where('is_internal_only', false);
    }

    /**
     * Scope to get only internal comments.
     */
    public function scopeInternalOnly($query)
    {
        return $query->where('is_internal_only', true);
    }

    /**
     * Check if this comment is visible to a specific user.
     */
    public function isVisibleTo(User $user): bool
    {
        // Internal employees can see everything
        if ($user->is_metatech_employee) {
            return true;
        }

        // Client users can only see non-internal comments
        return !$this->is_internal_only;
    }
}
