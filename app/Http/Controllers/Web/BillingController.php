<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use App\Models\Product;
use App\Services\ChangeCalculator;
use App\Services\OrderService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    /**
     * @var OrderService
     */
    protected $orderService;

    /**
     * @var ChangeCalculator
     */
    protected $changeCalculator;

    public function __construct(OrderService $orderService, ChangeCalculator $changeCalculator)
    {
        $this->orderService = $orderService;
        $this->changeCalculator = $changeCalculator;
    }

    /**
     * Show the store billing / new order screen.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $products = Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'price', 'tax_rate', 'stock_quantity']);

        $lowStockProducts = Product::query()
            ->lowStock()
            ->orderBy('stock_quantity')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'stock_quantity']);

        return view('billing.create', [
            'products' => $products,
            'lowStockProducts' => $lowStockProducts,
            'lowStockThreshold' => (int) config('inventory.low_stock_threshold', 10),
        ]);
    }

    /**
     * Auto-fill customer name when the email already exists.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function lookupCustomer(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $customer = Customer::query()->where('email', $data['email'])->first();

        return ApiResponse::success(
            $customer ? 'Customer found.' : 'Customer not found.',
            $customer ? [
                'id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email,
            ] : null
        );
    }

    /**
     * Store the bill: customer, order, order items, and stock deduction.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreOrderRequest $request)
    {
        $order = $this->orderService->create($request->validated());

        $payload = (new OrderResource($order))->resolve();
        $payload['change_breakdown'] = $this->changeCalculator->breakdown($order->change_due ?: 0);

        return ApiResponse::success('Bill generated successfully.', $payload, 201);
    }
}
