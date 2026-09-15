<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LowStockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_it_returns_products_below_the_configured_threshold()
    {
        Product::factory()->create([
            'name' => 'Keyboard',
            'sku' => 'KEY001',
            'stock_quantity' => 4,
        ]);

        Product::factory()->create([
            'name' => 'Mouse',
            'sku' => 'MOU001',
            'stock_quantity' => 10,
        ]);

        Product::factory()->create([
            'name' => 'Cable',
            'sku' => 'CBL001',
            'stock_quantity' => 25,
        ]);

        $response = $this->getJson('/api/products/low-stock');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'KEY001')
            ->assertJsonPath('data.0.stock_quantity', 4);

        $this->assertArrayNotHasKey('price', $response->json('data.0'));
    }

    public function test_threshold_can_be_changed_through_configuration()
    {
        config(['inventory.low_stock_threshold' => 20]);

        Product::factory()->create(['sku' => 'A001', 'stock_quantity' => 19]);
        Product::factory()->create(['sku' => 'B001', 'stock_quantity' => 20]);

        $this->getJson('/api/products/low-stock')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sku', 'A001');
    }
}
