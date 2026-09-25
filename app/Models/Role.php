<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    // ─── Relationships ─────────────────────────────

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')->withTimestamps();
    }

    // ─── Permission Helpers ────────────────────────

    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    public function givePermissionTo(string ...$permissionSlugs): void
    {
        $permissions = Permission::whereIn('slug', $permissionSlugs)->pluck('id');
        $this->permissions()->syncWithoutDetaching($permissions);
    }

    public function revokePermissionTo(string ...$permissionSlugs): void
    {
        $permissions = Permission::whereIn('slug', $permissionSlugs)->pluck('id');
        $this->permissions()->detach($permissions);
    }

    public function syncPermissions(array $permissionSlugs): void
    {
        $permissions = Permission::whereIn('slug', $permissionSlugs)->pluck('id');
        $this->permissions()->sync($permissions);
    }

    // ─── Scopes ────────────────────────────────────

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}