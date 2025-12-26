<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BootstrapState extends Model
{
    protected $fillable = [
        'status',
        'super_admin_email',
        'super_admin_id',
        'created_at',
        'confirmed_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    /**
     * Get the current bootstrap state (singleton pattern).
     *
     * @return self
     */
    public static function current(): self
    {
        return static::firstOrCreate(
            ['id' => 1],
            ['status' => 'BOOTSTRAP_PENDING']
        );
    }

    /**
     * Get the super admin user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function superAdmin()
    {
        return $this->belongsTo(User::class, 'super_admin_id');
    }
}
