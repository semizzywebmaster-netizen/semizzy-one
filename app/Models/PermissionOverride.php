<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PermissionOverride extends Model
{
    protected $fillable = [
        'user_id',
        'permission_id',
        'granted',
    ];

    protected function casts(): array
    {
        return [
            'granted' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
}