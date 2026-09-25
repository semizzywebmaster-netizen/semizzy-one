<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'module',
        'description',
    ];

    // ─── Relationships ─────────────────────────────

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role')->withTimestamps();
    }

    public function overrides()
    {
        return $this->hasMany(PermissionOverride::class);
    }

    // ─── Scopes ────────────────────────────────────

    public function scopeOfModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('module')->orderBy('name');
    }
}