<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_it_returns_order_history_for_a_customer_email()
    {
        $customer = Customer::factory()->create([
            'name' => 'Raveena',
            'email' => 'raveena@example.com',
        ]);

        $product = Product::factory()->create([
            'name' => 'Keyboard',
            'sku' => 'KEY001',
            'price' => 89.99,
        ]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'subtotal' => 89.99,
            'tax_amount' => 16.20,
            'total_amount' => 106.19,
            'status' => Order::STATUS_CONFIRMED,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 89.99,
            'tax_rate' => 18,
            'subtotal' => 89.99,
            'tax_amount' => 16.20,
            'total_amount' => 106.19,
        ]);

        $this->getJson('/api/orders/history?email=raveena@example.com')
            ->assertOk()
            ->assertJsonPath('data.0.id', $order->id)
            ->assertJsonPath('data.0.status', 'confirmed')
            ->assertJsonPath('data.0.subtotal', 89.99)
            ->assertJsonPath('data.0.tax_amount', 16.20)
            ->assertJsonPath('data.0.total_amount', 106.19)
            ->assertJsonPath('data.0.items.0.quantity', 1)
            ->assertJsonPath('data.0.items.0.unit_price', 89.99)
            ->assertJsonPath('data.0.items.0.product.sku', 'KEY001');
    }

    public function test_it_returns_not_found_when_the_customer_does_not_exist()
    {
        $this->getJson('/api/orders/history?email=missing@example.com')
            ->assertStatus(404)
            ->assertJsonPath('message', 'Customer not found.');
    }

    public function test_email_query_parameter_is_required()
    {
        $this->getJson('/api/orders/history')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
