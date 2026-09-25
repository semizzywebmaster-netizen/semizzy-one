<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderHealthLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'provider_id',
        'status',
        'response_time_ms',
        'message',
        'metadata',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'checked_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }
}