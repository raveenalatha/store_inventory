<?php

namespace App\Services;

use App\Exceptions\InsufficientPaymentException;
use App\Exceptions\InsufficientStockException;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    /**
     * @var OrderCalculator
     */
    protected $calculator;

    public function __construct(OrderCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Create an order, snapshot prices, and deduct stock atomically.
     *
     * Product rows are locked with lockForUpdate() in ascending ID order
     * so concurrent requests cannot oversell and deadlocks are less likely.
     *
     * @param  array{customer_name: string, customer_email: string, items: array<int, array{product_id: int, quantity: int}>}  $payload
     * @return \App\Models\Order
     *
     * @throws \App\Exceptions\InsufficientStockException
     */
    public function create(array $payload)
    {
        $order = DB::transaction(function () use ($payload) {
            $customer = $this->findOrCreateCustomer(
                $payload['customer_name'],
                $payload['customer_email']
            );

            $requestedQuantities = $this->aggregateQuantities($payload['items']);
            $products = $this->lockProducts(array_keys($requestedQuantities));

            $this->assertSufficientStock($products, $requestedQuantities);

            $preparedLines = [];
            $lineTotals = [];

            foreach ($payload['items'] as $item) {
                $product = $products->get((int) $item['product_id']);
                $quantity = (int) $item['quantity'];

                // Never trust client-supplied prices or tax rates.
                $unitPrice = (float) $product->price;
                $taxRate = (float) $product->tax_rate;
                $totals = $this->calculator->lineTotals($unitPrice, $taxRate, $quantity);

                $preparedLines[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $this->money($unitPrice),
                    'tax_rate' => $this->money($taxRate),
                    'subtotal' => $this->money($totals['subtotal']),
                    'tax_amount' => $this->money($totals['tax_amount']),
                    'total_amount' => $this->money($totals['total_amount']),
                ];

                $lineTotals[] = $totals;
            }

            $orderTotals = $this->calculator->orderTotals($lineTotals);

            $orderPayload = [
                'customer_id' => $customer->id,
                'subtotal' => $this->money($orderTotals['subtotal']),
                'tax_amount' => $this->money($orderTotals['tax_amount']),
                'total_amount' => $this->money($orderTotals['total_amount']),
                'status' => Order::STATUS_CONFIRMED,
            ];

            if (isset($payload['amount_given']) && $payload['amount_given'] !== null && $payload['amount_given'] !== '') {
                $amountGiven = round((float) $payload['amount_given'], 2);

                if ($amountGiven < $orderTotals['total_amount']) {
                    throw new InsufficientPaymentException($orderTotals['total_amount']);
                }

                $orderPayload['amount_given'] = $this->money($amountGiven);
                $orderPayload['change_due'] = $this->money($amountGiven - $orderTotals['total_amount']);
            }

            $order = Order::query()->create($orderPayload);

            $order->items()->createMany($preparedLines);

            foreach ($requestedQuantities as $productId => $quantity) {
                $products->get($productId)->decrement('stock_quantity', $quantity);
            }

            return $order;
        });

        $order->load(['customer', 'items.product']);

        Log::info('Order confirmation email sent', [
            'order_id' => $order->id,
            'customer_email' => optional($order->customer)->email,
        ]);

        return $order;
    }

    /**
     * @return \App\Models\Customer
     */
    protected function findOrCreateCustomer($name, $email)
    {
        $customer = Customer::query()->firstOrCreate(
            ['email' => $email],
            ['name' => $name]
        );

        if ($customer->name !== $name) {
            $customer->update(['name' => $name]);
        }

        return $customer;
    }

    /**
     * Combine duplicate product lines so stock is checked against the real total.
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $items
     * @return array<int, int>
     */
    protected function aggregateQuantities(array $items)
    {
        $quantities = [];

        foreach ($items as $item) {
            $productId = (int) $item['product_id'];
            $quantities[$productId] = ($quantities[$productId] ?? 0) + (int) $item['quantity'];
        }

        return $quantities;
    }

    /**
     * Lock product rows in a stable order until the surrounding transaction commits.
     *
     * @param  array<int, int>  $productIds
     * @return \Illuminate\Support\Collection<int, \App\Models\Product>
     */
    protected function lockProducts(array $productIds)
    {
        sort($productIds);

        return Product::query()
            ->whereIn('id', $productIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');
    }

    /**
     * @param  \Illuminate\Support\Collection<int, \App\Models\Product>  $products
     * @param  array<int, int>  $requestedQuantities
     * @return void
     *
     * @throws \App\Exceptions\InsufficientStockException
     */
    protected function assertSufficientStock($products, array $requestedQuantities)
    {
        $errors = [];

        foreach ($requestedQuantities as $productId => $quantity) {
            $product = $products->get($productId);

            if (!$product) {
                $errors['product_id'][] = 'Product '.$productId.' was not found.';
                continue;
            }

            if ($product->stock_quantity < $quantity) {
                $errors['product_id'][] = 'Only '.$product->stock_quantity.' units are available for this product.';
            }
        }

        if (!empty($errors)) {
            throw new InsufficientStockException($errors);
        }
    }

    /**
     * @param  float|int|string  $value
     * @return string
     */
    protected function money($value)
    {
        return number_format((float) $value, 2, '.', '');
    }
}
