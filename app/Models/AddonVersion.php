<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AddonVersion extends Model
{
    protected $fillable = [
        'addon_id',
        'version',
        'changelog',
        'migration_files',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'migration_files' => 'array',
            'applied_at' => 'datetime',
        ];
    }

    public function addon(): BelongsTo
    {
        return $this->belongsTo(Addon::class);
    }
}