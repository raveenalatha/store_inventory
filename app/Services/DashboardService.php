<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;

class DashboardService
{
    /**
     * @return array<string, int>
     */
    public function stats()
    {
        $threshold = (int) config('inventory.low_stock_threshold', 10);

        return [
            'total_products' => Product::query()->count(),
            'low_stock_products' => Product::query()->lowStock($threshold)->count(),
            'total_orders' => Order::query()->count(),
            'total_customers' => Customer::query()->count(),
            'low_stock_threshold' => $threshold,
        ];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function lowStockProducts($limit = 10)
    {
        return Product::query()
            ->lowStock()
            ->orderBy('stock_quantity')
            ->limit($limit)
            ->get();
    }
}
