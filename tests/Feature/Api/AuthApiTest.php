<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
    }

    public function test_api_health_endpoint_returns_success(): void
    {
        $response = $this->getJson('/api/v1/health');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_api_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@semizzy.com',
            'password' => 'admin123',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure([
            'data' => ['user', 'token'],
        ]);
    }

    public function test_api_login_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@semizzy.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
        $response->assertJson(['success' => false]);
    }

    public function test_api_me_endpoint_returns_authenticated_user(): void
    {
        $user = User::where('email', 'admin@semizzy.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'email' => 'admin@semizzy.com',
                'name' => 'Admin',
            ],
        ]);
    }

    public function test_api_me_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/auth/me');
        $response->assertStatus(401);
    }

    public function test_api_logout_revokes_token(): void
    {
        $user = User::where('email', 'admin@semizzy.com')->first();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200);

        // Verify token is deleted from DB
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_api_login_validation(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'not-an-email',
            'password' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }
}