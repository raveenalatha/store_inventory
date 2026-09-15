<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_displayed()
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Store Inventory')
            ->assertSee('Email')
            ->assertSee('Password');
    }

    public function test_web_login_redirects_to_the_dashboard()
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Total Products')
            ->assertSee('Low Stock Products')
            ->assertSee('Total Orders')
            ->assertSee('Total Customers');
    }

    public function test_web_login_shows_validation_errors()
    {
        $this->from('/login')
            ->post('/login', [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ])->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_dashboard_requires_authentication()
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }
}
