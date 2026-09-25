<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'credentials',
        'config',
        'status',
        'priority',
        'timeout',
        'max_retries',
        'health_check_enabled',
        'health_status',
        'last_health_check_at',
        'last_error',
    ];

    protected $hidden = [
        'credentials',
    ];

    protected function casts(): array
    {
        return [
            'credentials' => 'encrypted:array',
            'config' => 'array',
            'health_check_enabled' => 'boolean',
            'last_health_check_at' => 'datetime',
        ];
    }

    public function healthLogs(): HasMany
    {
        return $this->hasMany(ProviderHealthLog::class);
    }

    // ─── Scopes ────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    public function scopeHealthy($query)
    {
        return $query->where('health_status', 'healthy');
    }
}