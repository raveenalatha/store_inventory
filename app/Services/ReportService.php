<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportService
{
    /**
     * Orders and totals for a date range (inclusive).
     *
     * @param string $from
     * @param string $to
     * @return array<string, mixed>
     */
    public function forDateRange($from, $to)
    {
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();

        /** @var Collection $orders */
        $orders = Order::query()
            ->with(['customer', 'items'])
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->get();

        return [
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'orders' => $orders,
            'summary' => [
                'order_count' => $orders->count(),
                'item_count' => $orders->sum(function (Order $order) {
                    return $order->items->sum('quantity');
                }),
                'subtotal' => $orders->sum('subtotal'),
                'tax_amount' => $orders->sum('tax_amount'),
                'total_amount' => $orders->sum('total_amount'),
            ],
        ];
    }
}
