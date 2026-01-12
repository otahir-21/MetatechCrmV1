<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'assigned_to',
        'assigned_by',
        'created_by',
        'due_date',
        'start_date',
        'completed_at',
        'position',
        'tags',
        'checklist',
        'attachments',
        'is_pinned',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'start_date' => 'datetime',
        'completed_at' => 'datetime',
        'tags' => 'array',
        'checklist' => 'array',
        'attachments' => 'array',
        'is_pinned' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->whereNull('parent_comment_id')->orderBy('created_at', 'asc');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at', 'asc');
    }

    // Helper methods
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'done';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'done';
    }

    public function getProgressPercentage(): int
    {
        if (!$this->checklist || empty($this->checklist)) {
            return $this->isCompleted() ? 100 : 0;
        }

        $completed = collect($this->checklist)->where('checked', true)->count();
        $total = count($this->checklist);
        
        return $total > 0 ? (int) (($completed / $total) * 100) : 0;
    }
}
