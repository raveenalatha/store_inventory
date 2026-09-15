<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OrderHistoryRequest;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
use App\Services\OrderService;
use App\Support\ApiResponse;

class OrderController extends Controller
{
    /**
     * @var OrderService
     */
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * Create an order and deduct stock inside a locked transaction.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreOrderRequest $request)
    {
        $order = $this->orderService->create($request->validated());

        return ApiResponse::success(
            'Order created successfully.',
            (new OrderResource($order))->resolve(),
            201
        );
    }

    /**
     * Return a customer's order history by email.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function history(OrderHistoryRequest $request)
    {
        $customer = Customer::query()
            ->where('email', $request->query('email'))
            ->first();

        if (!$customer) {
            return ApiResponse::error('Customer not found.', [
                'email' => ['No customer exists with this email address.'],
            ], 404);
        }

        $orders = $customer->orders()
            ->with(['customer', 'items.product'])
            ->latest()
            ->get();

        return ApiResponse::success(
            'Order history retrieved successfully.',
            OrderResource::collection($orders)->resolve()
        );
    }
}
