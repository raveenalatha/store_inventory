<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcurrencyProtectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Sequential proof that stock of 1 cannot be sold twice.
     *
     * True parallel requests are difficult in default PHPUnit (single process).
     * See README.md "Concurrency protection" for a two-HTTP-request reproduction
     * against MySQL InnoDB, which is required for row-level lockForUpdate().
     *
     * @return void
     */
    public function test_only_one_order_succeeds_when_stock_is_one()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create([
            'stock_quantity' => 1,
        ]);

        $payload = [
            'customer_name' => 'Raveena',
            'customer_email' => 'raveena@example.com',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ];

        $first = $this->postJson('/api/orders', $payload);
        $second = $this->postJson('/api/orders', $payload);

        $statuses = [$first->status(), $second->status()];
        sort($statuses);

        $this->assertSame([201, 422], $statuses);
        $this->assertSame(1, Order::query()->count());
        $this->assertDatabaseCount('order_items', 1);
        $this->assertSame(0, $product->fresh()->stock_quantity);
        $this->assertGreaterThanOrEqual(0, $product->fresh()->stock_quantity);
    }

    public function test_order_creation_locks_product_rows_for_update()
    {
        $this->actingAs(User::factory()->create());

        $product = Product::factory()->create([
            'stock_quantity' => 5,
        ]);

        $sql = [];

        \Illuminate\Support\Facades\DB::listen(function ($query) use (&$sql) {
            $sql[] = strtolower($query->sql);
        });

        $this->postJson('/api/orders', [
            'customer_name' => 'Raveena',
            'customer_email' => 'raveena@example.com',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 1],
            ],
        ])->assertCreated();

        $productQuery = collect($sql)->first(function ($statement) {
            return strpos($statement, 'from "products"') !== false
                || strpos($statement, 'from `products`') !== false;
        });

        $this->assertNotNull($productQuery, 'Product rows must be queried while creating an order.');

        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'mysql') {
            $this->assertNotFalse(
                strpos($productQuery, 'for update'),
                'MySQL order creation must lock product rows with FOR UPDATE.'
            );
        }
    }
}
