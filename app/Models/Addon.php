<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Addon extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'author',
        'author_email',
        'homepage',
        'license',
        'requires',
        'permissions',
        'settings',
        'config',
        'status',
        'sort_order',
        'installed_at',
        'activated_at',
        'last_health_check_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'requires' => 'array',
            'permissions' => 'array',
            'settings' => 'array',
            'config' => 'array',
            'installed_at' => 'datetime',
            'activated_at' => 'datetime',
            'last_health_check_at' => 'datetime',
        ];
    }

    public function versions(): HasMany
    {
        return $this->hasMany(AddonVersion::class);
    }

    // ─── Status Helpers ────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isInstalled(): bool
    {
        return in_array($this->status, ['installed', 'active', 'inactive']);
    }

    // ─── Scopes ────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInstalled($query)
    {
        return $query->whereIn('status', ['installed', 'active', 'inactive']);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}