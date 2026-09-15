<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPageTest extends TestCase
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

    public function test_product_page_requires_authentication()
    {
        auth()->logout();

        $this->get('/products')->assertRedirect('/login');
    }

    public function test_product_menu_and_add_form_are_shown()
    {
        Product::factory()->create([
            'name' => 'Mechanical Keyboard',
            'sku' => 'KEY001',
            'description' => 'RGB mechanical keyboard',
            'price' => 89.99,
            'tax_rate' => 18,
            'stock_quantity' => 4,
        ]);

        $this->get('/products')
            ->assertOk()
            ->assertSee('Products')
            ->assertSee('Add product')
            ->assertSee('Name')
            ->assertSee('SKU')
            ->assertSee('Description')
            ->assertSee('Price')
            ->assertSee('Tax rate')
            ->assertSee('Stock quantity')
            ->assertSee('Save Product')
            ->assertSee('Mechanical Keyboard')
            ->assertSee('KEY001')
            ->assertSee('RGB mechanical keyboard');
    }

    public function test_it_creates_a_product()
    {
        $this->from('/products')
            ->post('/products', [
                'name' => 'Wireless Mouse',
                'sku' => 'MOU001',
                'description' => 'Ergonomic wireless mouse',
                'price' => 24.55,
                'tax_rate' => 18,
                'stock_quantity' => 31,
            ])->assertRedirect('/products')
            ->assertSessionHas('status', 'Product created.');

        $this->assertDatabaseHas('products', [
            'name' => 'Wireless Mouse',
            'sku' => 'MOU001',
            'description' => 'Ergonomic wireless mouse',
            'stock_quantity' => 31,
        ]);
    }

    public function test_duplicate_sku_is_not_accepted()
    {
        Product::factory()->create([
            'sku' => 'KEY001',
        ]);

        $this->from('/products')
            ->post('/products', [
                'name' => 'Another Keyboard',
                'sku' => 'KEY001',
                'description' => 'Duplicate SKU',
                'price' => 10,
                'tax_rate' => 0,
                'stock_quantity' => 1,
            ])->assertRedirect('/products')
            ->assertSessionHasErrors('sku');

        $this->assertSame(1, Product::query()->where('sku', 'KEY001')->count());
    }
}
