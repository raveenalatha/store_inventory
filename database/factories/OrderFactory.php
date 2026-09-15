<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * @var string
     */
    protected $model = Order::class;

    /**
     * @return array<string, mixed>
     */
    public function definition()
    {
        $subtotal = $this->faker->randomFloat(2, 20, 400);
        $taxAmount = round($subtotal * 0.18, 2);

        return [
            'customer_id' => Customer::factory(),
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => round($subtotal + $taxAmount, 2),
            'status' => Order::STATUS_CONFIRMED,
        ];
    }
}
