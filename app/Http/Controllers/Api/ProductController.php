<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Support\ApiResponse;

class ProductController extends Controller
{
    /**
     * List products below the configured LOW_STOCK_THRESHOLD.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function lowStock()
    {
        $products = Product::query()
            ->lowStock()
            ->orderBy('stock_quantity')
            ->orderBy('name')
            ->get();

        return ApiResponse::success(
            'Low stock products retrieved successfully.',
            ProductResource::collection($products)->resolve()
        );
    }
}
