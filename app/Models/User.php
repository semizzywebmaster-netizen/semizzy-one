<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'password',
        'status',
        'timezone',
        'locale',
        'two_factor_enabled',
        'last_login_ip',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'password' => 'hashed',
        ];
    }

    // ─── Relationships ─────────────────────────────

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    public function permissionOverrides(): HasMany
    {
        return $this->hasMany(PermissionOverride::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }

    // ─── Role / Permission Helpers ─────────────────

    public function hasRole(string ...$roleNames): bool
    {
        return $this->roles()->whereIn('slug', $roleNames)->exists();
    }

    public function hasPermission(string $permissionSlug): bool
    {
        // Check override first
        $override = $this->permissionOverrides()
            ->whereHas('permission', fn ($q) => $q->where('slug', $permissionSlug))
            ->first();

        if ($override) {
            return $override->granted;
        }

        // Check through roles
        return $this->roles()
            ->whereHas('permissions', fn ($q) => $q->where('slug', $permissionSlug))
            ->exists();
    }

    public function hasAnyPermission(string ...$permissions): bool
    {
        foreach ($permissions as $perm) {
            if ($this->hasPermission($perm)) {
                return true;
            }
        }
        return false;
    }

    public function hasAllPermissions(string ...$permissions): bool
    {
        foreach ($permissions as $perm) {
            if (!$this->hasPermission($perm)) {
                return false;
            }
        }
        return true;
    }

    public function getAllPermissions(): array
    {
        // Get permissions from roles
        $rolePermissions = $this->roles()
            ->with('permissions')
            ->get()
            ->flatMap(fn ($role) => $role->permissions->pluck('slug'))
            ->unique()
            ->toArray();

        // Apply overrides
        $overrides = $this->permissionOverrides()->with('permission')->get();
        foreach ($overrides as $override) {
            $slug = $override->permission->slug;
            if ($override->granted) {
                $rolePermissions[$slug] = $slug;
            } else {
                unset($rolePermissions[$slug]);
            }
        }

        return array_values($rolePermissions);
    }

    public function assignRole(string ...$roleNames): void
    {
        $roles = Role::whereIn('slug', $roleNames)->pluck('id');
        $this->roles()->syncWithoutDetaching($roles);
    }

    public function removeRole(string ...$roleNames): void
    {
        $roles = Role::whereIn('slug', $roleNames)->pluck('id');
        $this->roles()->detach($roles);
    }

    public function syncRoles(array $roleNames): void
    {
        $roles = Role::whereIn('slug', $roleNames)->pluck('id');
        $this->roles()->sync($roles);
    }

    // ─── Scopes ────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    // ─── Factory ───────────────────────────────────

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}