<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingPageTest extends TestCase
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

    public function test_billing_page_requires_authentication()
    {
        auth()->logout();

        $this->get('/orders/create')->assertRedirect('/login');
    }

    public function test_billing_page_shows_add_product_controls()
    {
        Product::factory()->create([
            'name' => 'Colgate Toothpaste',
            'sku' => 'COL001',
            'stock_quantity' => 4,
        ]);

        $this->get('/orders/create')
            ->assertOk()
            ->assertSee('New Order')
            ->assertSee('Add Product')
            ->assertSee('Generate Bill')
            ->assertSee('Low stock')
            ->assertSee('Colgate Toothpaste');
    }

    public function test_customer_lookup_autofills_existing_name()
    {
        Customer::factory()->create([
            'name' => 'Thomas',
            'email' => 'thomas@example.com',
        ]);

        $this->getJson('/customers/lookup?email=thomas@example.com')
            ->assertOk()
            ->assertJsonPath('data.name', 'Thomas');
    }

    public function test_generate_bill_stores_customer_order_and_items()
    {
        $product = Product::factory()->create([
            'name' => 'Colgate Toothpaste',
            'price' => 50,
            'tax_rate' => 8,
            'stock_quantity' => 10,
        ]);

        $response = $this->postJson('/orders', [
            'customer_name' => 'Thomas',
            'customer_email' => 'thomas@example.com',
            'amount_given' => 250,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.customer.email', 'thomas@example.com')
            ->assertJsonPath('data.amount_given', 250);

        $this->assertDatabaseHas('customers', [
            'email' => 'thomas@example.com',
            'name' => 'Thomas',
        ]);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertSame(8, $product->fresh()->stock_quantity);

        $order = Order::query()->first();
        $this->assertEquals(100, (float) $order->subtotal);
        $this->assertEquals(8, (float) $order->tax_amount);
        $this->assertEquals(108, (float) $order->total_amount);
        $this->assertEquals(142, (float) $order->change_due);
    }

    public function test_generate_bill_rejects_short_payment()
    {
        $product = Product::factory()->create([
            'price' => 50,
            'tax_rate' => 8,
            'stock_quantity' => 10,
        ]);

        $this->postJson('/orders', [
            'customer_name' => 'Thomas',
            'customer_email' => 'thomas@example.com',
            'amount_given' => 10,
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ])->assertStatus(422);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, $product->fresh()->stock_quantity);
    }
}
