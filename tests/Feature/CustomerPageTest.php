<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \App\Models\User
     */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_customer_page_requires_authentication()
    {
        auth()->logout();

        $this->get('/customers')->assertRedirect('/login');
    }

    public function test_customer_menu_and_add_form_are_shown()
    {
        $this->get('/customers')
            ->assertOk()
            ->assertSee('Customer')
            ->assertSee('Add customer')
            ->assertSee('Email ID')
            ->assertSee('Name')
            ->assertSee('Password')
            ->assertSee('Save Customer');
    }

    public function test_it_creates_a_customer_with_a_hashed_password()
    {
        $this->from('/customers')
            ->post('/customers', [
                'name' => 'Raveena',
                'email' => 'raveena.sdc@chettinad.com',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])->assertRedirect('/customers')
            ->assertSessionHas('status', 'Customer created.');

        $this->assertDatabaseHas('customers', [
            'name' => 'Raveena',
            'email' => 'raveena.sdc@chettinad.com',
        ]);

        $customer = Customer::query()->where('email', 'raveena.sdc@chettinad.com')->first();
        $this->assertNotNull($customer);
        $this->assertTrue(Hash::check('secret123', $customer->password));
        $this->assertNotSame('secret123', $customer->password);
    }

    public function test_duplicate_email_id_is_not_accepted()
    {
        Customer::factory()->create([
            'email' => 'raveena.sdc@chettinad.com',
        ]);

        $this->from('/customers')
            ->post('/customers', [
                'name' => 'Raveena Two',
                'email' => 'raveena.sdc@chettinad.com',
                'password' => 'secret123',
                'password_confirmation' => 'secret123',
            ])->assertRedirect('/customers')
            ->assertSessionHasErrors('email');

        $this->assertSame(1, Customer::query()->where('email', 'raveena.sdc@chettinad.com')->count());
    }
}
