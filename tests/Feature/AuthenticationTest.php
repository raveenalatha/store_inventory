<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_succeeds_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Login successful.')
            ->assertJsonPath('data.user.email', $user->email)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'user' => ['id', 'name', 'email'],
                ],
                'errors',
            ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_api_login_fails_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Invalid credentials.');
    }

    public function test_protected_apis_reject_unauthenticated_requests()
    {
        $this->postJson('/api/orders', [])->assertStatus(401);
        $this->getJson('/api/orders/history?email=raveena@example.com')->assertStatus(401);
        $this->getJson('/api/products/low-stock')->assertStatus(401);
        $this->getJson('/api/me')->assertStatus(401);
        $this->postJson('/api/logout')->assertStatus(401);
    }

    public function test_me_returns_the_authenticated_user()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email)
            ->assertJsonPath('data.name', $user->name);
    }

    public function test_logout_ends_the_session()
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertOk();

        $this->postJson('/api/logout')->assertOk();

        $this->assertGuest();

        $this->getJson('/api/me')->assertStatus(401);
    }
}
