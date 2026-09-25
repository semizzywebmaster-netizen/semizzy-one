<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('SEMIZZY ONE');
    }

    public function test_register_page_is_accessible(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
        $response->assertSee('Create your account');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $response = $this->post('/login', [
            'email' => 'admin@semizzy.com',
            'password' => 'admin123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_user_cannot_login_with_invalid_credentials(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $response = $this->post('/login', [
            'email' => 'admin@semizzy.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_user_can_register(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $user = User::where('email', 'admin@semizzy.com')->first();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Welcome back');
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_user_can_logout(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $user = User::where('email', 'admin@semizzy.com')->first();

        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect('/');
        $this->assertGuest();
    }
}