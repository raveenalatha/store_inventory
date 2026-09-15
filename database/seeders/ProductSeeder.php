<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        $products = [
            ['name' => 'Mechanical Keyboard', 'sku' => 'KEY001', 'description' => 'RGB mechanical keyboard', 'price' => 89.99, 'tax_rate' => 18.00, 'stock_quantity' => 4],
            ['name' => 'Wireless Mouse', 'sku' => 'MOU001', 'description' => 'Ergonomic wireless mouse', 'price' => 24.50, 'tax_rate' => 18.00, 'stock_quantity' => 35],
            ['name' => 'USB-C Hub', 'sku' => 'HUB001', 'description' => '7-in-1 USB-C hub', 'price' => 49.00, 'tax_rate' => 12.00, 'stock_quantity' => 18],
            ['name' => '27-inch Monitor', 'sku' => 'MON001', 'description' => 'QHD IPS monitor', 'price' => 279.00, 'tax_rate' => 18.00, 'stock_quantity' => 9],
            ['name' => 'Laptop Stand', 'sku' => 'STD001', 'description' => 'Aluminum laptop stand', 'price' => 32.00, 'tax_rate' => 5.00, 'stock_quantity' => 50],
            ['name' => 'Noise Cancelling Headphones', 'sku' => 'HP001', 'description' => 'Over-ear wireless headphones', 'price' => 159.99, 'tax_rate' => 18.00, 'stock_quantity' => 7],
            ['name' => 'Webcam 1080p', 'sku' => 'CAM001', 'description' => 'Full HD webcam with mic', 'price' => 59.00, 'tax_rate' => 12.00, 'stock_quantity' => 22],
            ['name' => 'Desk Lamp', 'sku' => 'LMP001', 'description' => 'LED desk lamp', 'price' => 18.75, 'tax_rate' => 5.00, 'stock_quantity' => 60],
            ['name' => 'HDMI Cable', 'sku' => 'CBL001', 'description' => '2 metre HDMI 2.0 cable', 'price' => 8.99, 'tax_rate' => 0.00, 'stock_quantity' => 120],
            ['name' => 'Notebook Pack', 'sku' => 'NB001', 'description' => 'Pack of 5 ruled notebooks', 'price' => 6.50, 'tax_rate' => 5.00, 'stock_quantity' => 3],
            ['name' => 'Office Chair', 'sku' => 'CHR001', 'description' => 'Mesh ergonomic chair', 'price' => 199.00, 'tax_rate' => 18.00, 'stock_quantity' => 12],
            ['name' => 'Sticky Notes', 'sku' => 'STK001', 'description' => 'Assorted sticky notes', 'price' => 2.49, 'tax_rate' => 0.00, 'stock_quantity' => 80],
        ];

        foreach ($products as $product) {
            Product::query()->firstOrCreate(
                ['sku' => $product['sku']],
                array_merge($product, ['is_active' => true])
            );
        }
    }
}
