<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPageTest extends TestCase
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
            'name' => 'Raam',
        ]);
        $this->actingAs($this->user);
    }

    public function test_report_page_requires_authentication()
    {
        auth()->logout();

        $this->get('/reports')->assertRedirect('/login');
    }

    public function test_authenticated_layout_shows_header_sidebar_and_logout()
    {
        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('Store Inventory')
            ->assertSee('Raam')
            ->assertSee('Logout')
            ->assertSee('Dashboard')
            ->assertSee('Customer')
            ->assertSee('Products')
            ->assertSee('New Order')
            ->assertSee('Report');
    }

    public function test_report_lists_orders_between_selected_dates()
    {
        $inRange = Order::factory()->create([
            'total_amount' => 108,
            'created_at' => '2026-09-05 10:00:00',
        ]);
        $inRange->customer->update([
            'name' => 'In Range Buyer',
            'email' => 'inrange@example.com',
        ]);

        $outOfRange = Order::factory()->create([
            'total_amount' => 50,
            'created_at' => '2026-08-01 10:00:00',
        ]);
        $outOfRange->customer->update([
            'name' => 'Out Of Range Buyer',
            'email' => 'outofrange@example.com',
        ]);

        $this->get('/reports?from=2026-09-01&to=2026-09-10')
            ->assertOk()
            ->assertSee('From date')
            ->assertSee('To date')
            ->assertSee('Show Report')
            ->assertSee('In Range Buyer')
            ->assertSee('inrange@example.com')
            ->assertSee('₹108.00')
            ->assertDontSee('Out Of Range Buyer')
            ->assertDontSee('outofrange@example.com');
    }

    public function test_report_excludes_orders_outside_the_date_range()
    {
        $outside = Order::factory()->create([
            'created_at' => '2026-01-15 09:00:00',
        ]);
        $outside->customer->update([
            'name' => 'January Customer',
            'email' => 'january@example.com',
        ]);

        $this->get('/reports?from=2026-09-01&to=2026-09-11')
            ->assertOk()
            ->assertSee('No orders in this date range.')
            ->assertDontSee('January Customer')
            ->assertDontSee('january@example.com');
    }

    public function test_report_rejects_an_end_date_before_the_start_date()
    {
        $this->from('/reports')
            ->get('/reports?from=2026-09-10&to=2026-09-01')
            ->assertRedirect('/reports')
            ->assertSessionHasErrors('to');
    }
}
