<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditService
{
    public static function log(
        string $action,
        ?string $targetType = null,
        ?int $targetId = null,
        ?string $result = 'success',
        ?array $metadata = null
    ): AuditLog {
        $request = request();
        $user = $request->user();

        return AuditLog::create([
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'System',
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'result' => $result,
            'metadata' => $metadata,
        ]);
    }

    public static function login(string $email, bool $success, ?string $reason = null): void
    {
        static::log(
            $success ? 'auth.login.success' : 'auth.login.failure',
            'user',
            null,
            $success ? 'success' : 'failure',
            ['email' => $email, 'reason' => $reason]
        );
    }

    public static function logout(?int $userId = null): void
    {
        static::log('auth.logout', 'user', $userId);
    }

    public static function passwordChanged(int $userId): void
    {
        static::log('auth.password.changed', 'user', $userId);
    }

    public static function userCreated(int $userId): void
    {
        static::log('user.created', 'user', $userId);
    }

    public static function userUpdated(int $userId): void
    {
        static::log('user.updated', 'user', $userId);
    }

    public static function userDeleted(int $userId): void
    {
        static::log('user.deleted', 'user', $userId);
    }

    public static function roleCreated(int $roleId): void
    {
        static::log('role.created', 'role', $roleId);
    }

    public static function roleUpdated(int $roleId): void
    {
        static::log('role.updated', 'role', $roleId);
    }

    public static function settingsUpdated(string $group): void
    {
        static::log('settings.updated', 'setting', null, 'success', ['group' => $group]);
    }

    public static function addonInstalled(string $slug): void
    {
        static::log('addon.installed', 'addon', null, 'success', ['slug' => $slug]);
    }

    public static function addonActivated(string $slug): void
    {
        static::log('addon.activated', 'addon', null, 'success', ['slug' => $slug]);
    }

    public static function addonDeactivated(string $slug): void
    {
        static::log('addon.deactivated', 'addon', null, 'success', ['slug' => $slug]);
    }

    public static function addonUninstalled(string $slug): void
    {
        static::log('addon.uninstalled', 'addon', null, 'success', ['slug' => $slug]);
    }
}