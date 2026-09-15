<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * @return void
     */
    public function run()
    {
        if (Order::query()->exists()) {
            return;
        }

        $orderService = app(OrderService::class);

        $samples = [
            [
                'customer_name' => 'Raveena',
                'customer_email' => 'raveena@example.com',
                'items' => [
                    ['product_id' => $this->productId('MOU001'), 'quantity' => 1],
                    ['product_id' => $this->productId('CBL001'), 'quantity' => 2],
                ],
            ],
            [
                'customer_name' => 'Arun Kumar',
                'customer_email' => 'arun.kumar@example.com',
                'items' => [
                    ['product_id' => $this->productId('HUB001'), 'quantity' => 1],
                ],
            ],
            [
                'customer_name' => 'Priya Nair',
                'customer_email' => 'priya.nair@example.com',
                'items' => [
                    ['product_id' => $this->productId('STD001'), 'quantity' => 1],
                    ['product_id' => $this->productId('LMP001'), 'quantity' => 1],
                ],
            ],
        ];

        foreach ($samples as $sample) {
            $orderService->create($sample);
        }
    }

    /**
     * @param  string  $sku
     * @return int
     */
    protected function productId($sku)
    {
        return \App\Models\Product::query()->where('sku', $sku)->firstOrFail()->id;
    }
}
