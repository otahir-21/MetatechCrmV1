<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BootstrapAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'action',
        'result',
        'ip_address',
        'user_id',
        'email',
        'request_payload',
        'error_message',
        'created_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user that performed the action.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
