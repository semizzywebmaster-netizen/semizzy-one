<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_roles_are_seeded_correctly(): void
    {
        $this->assertDatabaseHas('roles', ['slug' => 'admin']);
        $this->assertDatabaseHas('roles', ['slug' => 'staff']);
        $this->assertDatabaseHas('roles', ['slug' => 'support']);
        $this->assertDatabaseHas('roles', ['slug' => 'user']);
    }

    public function test_no_super_admin_role_exists(): void
    {
        $this->assertDatabaseMissing('roles', ['slug' => 'super_admin']);
        $this->assertDatabaseMissing('roles', ['name' => 'SUPER_ADMIN']);
    }

    public function test_admin_has_all_permissions(): void
    {
        $admin = User::where('email', 'admin@semizzy.com')->first();
        $totalPermissions = Permission::count();

        $this->assertTrue($admin->hasRole('admin'));
        $this->assertEquals($totalPermissions, count($admin->getAllPermissions()));
    }

    public function test_user_role_has_basic_permissions_only(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        $this->assertTrue($user->hasPermission('profile.view'));
        $this->assertTrue($user->hasPermission('profile.edit'));
        $this->assertFalse($user->hasPermission('users.view'));
        $this->assertFalse($user->hasPermission('dashboard.view'));
    }

    public function test_permission_override_works(): void
    {
        $user = User::factory()->create();
        $user->assignRole('user');

        // User normally can't view users
        $this->assertFalse($user->hasPermission('users.view'));

        // Grant via override
        $permission = Permission::where('slug', 'users.view')->first();
        $user->permissionOverrides()->create([
            'permission_id' => $permission->id,
            'granted' => true,
        ]);

        $this->assertTrue($user->hasPermission('users.view'));

        // Revoke via override
        $user->permissionOverrides()->update(['granted' => false]);

        // Need to clear any cached relations
        $user->load('permissionOverrides');
        $this->assertFalse($user->fresh()->hasPermission('users.view'));
    }

    public function test_admin_can_access_admin_dashboard(): void
    {
        $admin = User::where('email', 'admin@semizzy.com')->first();

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);
    }

    public function test_regular_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        $user->assignRole('user');

        $response = $this->actingAs($user)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_staff_cannot_access_admin_dashboard(): void
    {
        $staff = User::factory()->create(['status' => 'active']);
        $staff->assignRole('staff');

        $response = $this->actingAs($staff)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_system_roles_cannot_be_deleted(): void
    {
        $admin = User::where('email', 'admin@semizzy.com')->first();
        $adminRole = Role::where('slug', 'admin')->first();

        $response = $this->actingAs($admin)->delete('/admin/roles/' . $adminRole->id);
        $response->assertSessionHas('error');
    }

    public function test_permission_role_table_does_not_contain_user_id(): void
    {
        // Verify the schema
        $columns = \DB::select("SHOW COLUMNS FROM permission_role");
        $columnNames = array_column($columns, 'Field');

        $this->assertContains('role_id', $columnNames);
        $this->assertContains('permission_id', $columnNames);
        $this->assertNotContains('user_id', $columnNames);
    }
}