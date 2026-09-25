<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedRoles();
        $this->seedPermissions();
        $this->seedRolePermissions();
        $this->seedAdminUser();
        $this->seedCoreSettings();
    }

    private function seedRoles(): void
    {
        $roles = [
            ['name' => 'Administrator', 'slug' => 'admin', 'description' => 'Full system administrator with all permissions except super admin bypass', 'is_system' => true, 'sort_order' => 1],
            ['name' => 'Staff', 'slug' => 'staff', 'description' => 'Staff member with limited administrative capabilities', 'is_system' => true, 'sort_order' => 2],
            ['name' => 'Support', 'slug' => 'support', 'description' => 'Support team member with user assistance permissions', 'is_system' => true, 'sort_order' => 3],
            ['name' => 'User', 'slug' => 'user', 'description' => 'Regular user with basic platform access', 'is_system' => true, 'sort_order' => 4],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }

    private function seedPermissions(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'View Dashboard', 'slug' => 'dashboard.view', 'module' => 'dashboard', 'description' => 'Access the admin dashboard'],

            // User Management
            ['name' => 'View Users', 'slug' => 'users.view', 'module' => 'users', 'description' => 'View user listings'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'module' => 'users', 'description' => 'Create new users'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'module' => 'users', 'description' => 'Edit user details'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'module' => 'users', 'description' => 'Delete users'],
            ['name' => 'Suspend Users', 'slug' => 'users.suspend', 'module' => 'users', 'description' => 'Suspend/unsuspend users'],

            // Role Management
            ['name' => 'View Roles', 'slug' => 'roles.view', 'module' => 'roles', 'description' => 'View roles'],
            ['name' => 'Create Roles', 'slug' => 'roles.create', 'module' => 'roles', 'description' => 'Create new roles'],
            ['name' => 'Edit Roles', 'slug' => 'roles.edit', 'module' => 'roles', 'description' => 'Edit roles'],
            ['name' => 'Delete Roles', 'slug' => 'roles.delete', 'module' => 'roles', 'description' => 'Delete non-system roles'],

            // Permission Management
            ['name' => 'View Permissions', 'slug' => 'permissions.view', 'module' => 'permissions', 'description' => 'View permissions'],
            ['name' => 'Assign Permissions', 'slug' => 'permissions.assign', 'module' => 'permissions', 'description' => 'Assign permissions to roles'],

            // Settings
            ['name' => 'View Settings', 'slug' => 'settings.view', 'module' => 'settings', 'description' => 'View system settings'],
            ['name' => 'Edit Settings', 'slug' => 'settings.edit', 'module' => 'settings', 'description' => 'Edit system settings'],

            // Notifications
            ['name' => 'View Notifications', 'slug' => 'notifications.view', 'module' => 'notifications', 'description' => 'View notifications'],
            ['name' => 'Manage Notifications', 'slug' => 'notifications.manage', 'module' => 'notifications', 'description' => 'Manage notification settings'],

            // Audit Logs
            ['name' => 'View Audit Logs', 'slug' => 'audit.view', 'module' => 'audit', 'description' => 'View audit logs'],

            // Security
            ['name' => 'View Security', 'slug' => 'security.view', 'module' => 'security', 'description' => 'View security settings'],
            ['name' => 'Manage Security', 'slug' => 'security.manage', 'module' => 'security', 'description' => 'Manage security settings'],

            // System Health
            ['name' => 'View System Health', 'slug' => 'health.view', 'module' => 'health', 'description' => 'View system health status'],

            // Backup
            ['name' => 'View Backups', 'slug' => 'backups.view', 'module' => 'backups', 'description' => 'View backup status'],
            ['name' => 'Manage Backups', 'slug' => 'backups.manage', 'module' => 'backups', 'description' => 'Create and manage backups'],

            // Addons
            ['name' => 'View Addons', 'slug' => 'addons.view', 'module' => 'addons', 'description' => 'View installed addons'],
            ['name' => 'Manage Addons', 'slug' => 'addons.manage', 'module' => 'addons', 'description' => 'Install, activate, deactivate, and uninstall addons'],

            // Providers
            ['name' => 'View Providers', 'slug' => 'providers.view', 'module' => 'providers', 'description' => 'View providers'],
            ['name' => 'Manage Providers', 'slug' => 'providers.manage', 'module' => 'providers', 'description' => 'Manage provider configuration'],

            // Support
            ['name' => 'View Support', 'slug' => 'support.view', 'module' => 'support', 'description' => 'View support tickets'],
            ['name' => 'Manage Support', 'slug' => 'support.manage', 'module' => 'support', 'description' => 'Manage support tickets'],

            // Profile (for all users)
            ['name' => 'View Profile', 'slug' => 'profile.view', 'module' => 'profile', 'description' => 'View own profile'],
            ['name' => 'Edit Profile', 'slug' => 'profile.edit', 'module' => 'profile', 'description' => 'Edit own profile'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['slug' => $permission['slug']], $permission);
        }
    }

    private function seedRolePermissions(): void
    {
        // Admin gets all permissions
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(
                Permission::pluck('slug')->toArray()
            );
        }

        // Staff gets dashboard, user view, support
        $staffRole = Role::where('slug', 'staff')->first();
        if ($staffRole) {
            $staffRole->syncPermissions([
                'dashboard.view',
                'users.view',
                'users.edit',
                'notifications.view',
                'audit.view',
                'health.view',
                'support.view',
                'support.manage',
                'profile.view',
                'profile.edit',
            ]);
        }

        // Support gets user view, support, notifications
        $supportRole = Role::where('slug', 'support')->first();
        if ($supportRole) {
            $supportRole->syncPermissions([
                'dashboard.view',
                'users.view',
                'notifications.view',
                'support.view',
                'support.manage',
                'profile.view',
                'profile.edit',
            ]);
        }

        // User gets basic profile permissions
        $userRole = Role::where('slug', 'user')->first();
        if ($userRole) {
            $userRole->syncPermissions([
                'profile.view',
                'profile.edit',
            ]);
        }
    }

    private function seedAdminUser(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@semizzy.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin123'),
                'status' => 'active',
                'email_verified_at' => now(),
                'timezone' => 'Africa/Lagos',
                'locale' => 'en',
            ]
        );

        $admin->assignRole('admin');
    }

    private function seedCoreSettings(): void
    {
        $settings = [
            // Application
            ['group' => 'app', 'key' => 'name', 'value' => 'SEMIZZY ONE', 'type' => 'string', 'description' => 'Application name', 'is_public' => true],
            ['group' => 'app', 'key' => 'tagline', 'value' => 'Everything You Need. One Platform.', 'type' => 'string', 'description' => 'Application tagline', 'is_public' => true],
            ['group' => 'app', 'key' => 'logo', 'value' => null, 'type' => 'string', 'description' => 'Application logo path', 'is_public' => true],
            ['group' => 'app', 'key' => 'favicon', 'value' => null, 'type' => 'string', 'description' => 'Application favicon path', 'is_public' => true],
            ['group' => 'app', 'key' => 'version', 'value' => '2.0.0', 'type' => 'string', 'description' => 'Core version', 'is_public' => true],
            ['group' => 'app', 'key' => 'timezone', 'value' => 'Africa/Lagos', 'type' => 'string', 'description' => 'Default timezone', 'is_public' => false],
            ['group' => 'app', 'key' => 'locale', 'value' => 'en', 'type' => 'string', 'description' => 'Default locale', 'is_public' => true],
            ['group' => 'app', 'key' => 'date_format', 'value' => 'Y-m-d', 'type' => 'string', 'description' => 'Date format', 'is_public' => false],
            ['group' => 'app', 'key' => 'maintenance_mode', 'value' => 'false', 'type' => 'boolean', 'description' => 'Maintenance mode', 'is_public' => false],

            // Security
            ['group' => 'security', 'key' => 'max_login_attempts', 'value' => '5', 'type' => 'integer', 'description' => 'Max login attempts before lockout', 'is_public' => false],
            ['group' => 'security', 'key' => 'lockout_duration', 'value' => '900', 'type' => 'integer', 'description' => 'Lockout duration in seconds', 'is_public' => false],
            ['group' => 'security', 'key' => 'session_lifetime', 'value' => '120', 'type' => 'integer', 'description' => 'Session lifetime in minutes', 'is_public' => false],
            ['group' => 'security', 'key' => 'password_min_length', 'value' => '8', 'type' => 'integer', 'description' => 'Minimum password length', 'is_public' => true],

            // Notifications
            ['group' => 'notifications', 'key' => 'email_enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'Email notifications enabled', 'is_public' => false],
            ['group' => 'notifications', 'key' => 'sms_enabled', 'value' => 'false', 'type' => 'boolean', 'description' => 'SMS notifications enabled', 'is_public' => false],
            ['group' => 'notifications', 'key' => 'push_enabled', 'value' => 'false', 'type' => 'boolean', 'description' => 'Push notifications enabled', 'is_public' => false],

            // PWA
            ['group' => 'pwa', 'key' => 'enabled', 'value' => 'true', 'type' => 'boolean', 'description' => 'PWA enabled', 'is_public' => true],
            ['group' => 'pwa', 'key' => 'short_name', 'value' => 'SEMIZZY', 'type' => 'string', 'description' => 'PWA short name', 'is_public' => true],

            // Brand
            ['group' => 'brand', 'key' => 'primary_color', 'value' => '#155EEF', 'type' => 'string', 'description' => 'Primary brand color', 'is_public' => true],
            ['group' => 'brand', 'key' => 'teal_color', 'value' => '#00B8A9', 'type' => 'string', 'description' => 'Teal brand color', 'is_public' => true],
            ['group' => 'brand', 'key' => 'navy_color', 'value' => '#071A33', 'type' => 'string', 'description' => 'Navy brand color', 'is_public' => true],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['group' => $setting['group'], 'key' => $setting['key']],
                $setting
            );
        }
    }
}