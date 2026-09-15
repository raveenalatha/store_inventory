<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \App\Models\User
     */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'Raveena',
            'email' => 'raveena@example.com',
            'password' => Hash::make('password'),
        ]);
        $this->actingAs($this->user);
    }

    public function test_profile_menu_shows_logout_and_change_password()
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Account menu')
            ->assertSee('Raveena')
            ->assertSee('raveena@example.com')
            ->assertSee('Logout')
            ->assertSee('Change Password');
    }

    public function test_change_password_page_requires_authentication()
    {
        auth()->logout();

        $this->get('/password')->assertRedirect('/login');
    }

    public function test_user_can_change_password()
    {
        $this->from('/password')
            ->post('/password', [
                'current_password' => 'password',
                'password' => 'new-secret',
                'password_confirmation' => 'new-secret',
            ])->assertRedirect('/password')
            ->assertSessionHas('status', 'Password updated.');

        $this->assertTrue(Hash::check('new-secret', $this->user->fresh()->password));
    }

    public function test_change_password_rejects_incorrect_current_password()
    {
        $this->from('/password')
            ->post('/password', [
                'current_password' => 'wrong-password',
                'password' => 'new-secret',
                'password_confirmation' => 'new-secret',
            ])->assertRedirect('/password')
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('password', $this->user->fresh()->password));
    }
}
