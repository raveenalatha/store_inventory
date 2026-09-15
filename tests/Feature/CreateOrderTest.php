<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateOrderTest extends TestCase
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

    public function test_it_creates_an_order_and_decrements_stock()
    {
        $product = Product::factory()->create([
            'price' => 50.00,
            'tax_rate' => 10.00,
            'stock_quantity' => 10,
        ]);

        $response = $this->postJson('/api/orders', [
            'customer_name' => 'Raveena',
            'customer_email' => 'raveena@example.com',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('message', 'Order created successfully.')
            ->assertJsonPath('data.status', 'confirmed');

        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_items', 1);
        $this->assertSame(8, $product->fresh()->stock_quantity);
        $this->assertDatabaseHas('customers', [
            'email' => 'raveena@example.com',
            'name' => 'Raveena',
        ]);
    }

    public function test_it_rejects_insufficient_stock_without_creating_an_order()
    {
        $product = Product::factory()->create([
            'stock_quantity' => 2,
        ]);

        $this->postJson('/api/orders', [
            'customer_name' => 'Raveena',
            'customer_email' => 'raveena@example.com',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Insufficient stock')
            ->assertJsonPath('errors.product_id.0', 'Only 2 units are available for this product.');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(2, $product->fresh()->stock_quantity);
    }

    public function test_it_rejects_an_invalid_product()
    {
        $this->postJson('/api/orders', [
            'customer_name' => 'Raveena',
            'customer_email' => 'raveena@example.com',
            'items' => [
                ['product_id' => 999, 'quantity' => 1],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_id']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_quantity_cannot_be_zero()
    {
        $product = Product::factory()->create(['stock_quantity' => 10]);

        $this->postJson('/api/orders', [
            'customer_name' => 'Raveena',
            'customer_email' => 'raveena@example.com',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 0],
            ],
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);

        $this->assertSame(10, $product->fresh()->stock_quantity);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_it_calculates_tax_from_database_prices()
    {
        $product = Product::factory()->create([
            'price' => 100.00,
            'tax_rate' => 10.00,
            'stock_quantity' => 10,
        ]);

        $this->postJson('/api/orders', [
            'customer_name' => 'Raveena',
            'customer_email' => 'raveena@example.com',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.subtotal', 200)
            ->assertJsonPath('data.tax_amount', 20)
            ->assertJsonPath('data.total_amount', 220)
            ->assertJsonPath('data.items.0.unit_price', 100)
            ->assertJsonPath('data.items.0.tax_rate', 10)
            ->assertJsonPath('data.items.0.subtotal', 200)
            ->assertJsonPath('data.items.0.tax_amount', 20)
            ->assertJsonPath('data.items.0.total_amount', 220);
    }

    public function test_it_ignores_client_supplied_prices()
    {
        $product = Product::factory()->create([
            'price' => 40.00,
            'tax_rate' => 5.00,
            'stock_quantity' => 10,
        ]);

        $this->postJson('/api/orders', [
            'customer_name' => 'Raveena',
            'customer_email' => 'raveena@example.com',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'unit_price' => 1,
                    'tax_rate' => 0,
                ],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.subtotal', 40)
            ->assertJsonPath('data.tax_amount', 2)
            ->assertJsonPath('data.total_amount', 42);
    }

    public function test_it_creates_an_order_with_multiple_products()
    {
        $keyboard = Product::factory()->create([
            'name' => 'Keyboard',
            'price' => 50.00,
            'tax_rate' => 10.00,
            'stock_quantity' => 10,
        ]);

        $mouse = Product::factory()->create([
            'name' => 'Mouse',
            'price' => 30.00,
            'tax_rate' => 5.00,
            'stock_quantity' => 6,
        ]);

        $this->postJson('/api/orders', [
            'customer_name' => 'Raveena',
            'customer_email' => 'raveena@example.com',
            'items' => [
                ['product_id' => $keyboard->id, 'quantity' => 2],
                ['product_id' => $mouse->id, 'quantity' => 1],
            ],
        ])->assertCreated()
            ->assertJsonPath('data.subtotal', 130)
            ->assertJsonPath('data.tax_amount', 11.5)
            ->assertJsonPath('data.total_amount', 141.5)
            ->assertJsonCount(2, 'data.items');

        $this->assertSame(8, $keyboard->fresh()->stock_quantity);
        $this->assertSame(5, $mouse->fresh()->stock_quantity);
        $this->assertSame(1, Order::query()->count());
        $this->assertSame(1, Customer::query()->count());
    }

    public function test_insufficient_stock_on_one_item_rejects_the_entire_order()
    {
        $inStock = Product::factory()->create(['stock_quantity' => 10]);
        $lowStock = Product::factory()->create(['stock_quantity' => 1]);

        $this->postJson('/api/orders', [
            'customer_name' => 'Raveena',
            'customer_email' => 'raveena@example.com',
            'items' => [
                ['product_id' => $inStock->id, 'quantity' => 1],
                ['product_id' => $lowStock->id, 'quantity' => 5],
            ],
        ])->assertStatus(422);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, $inStock->fresh()->stock_quantity);
        $this->assertSame(1, $lowStock->fresh()->stock_quantity);
    }
}
