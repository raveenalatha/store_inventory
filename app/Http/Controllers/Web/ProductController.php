<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\StoreProductRequest;
use App\Models\Product;

class ProductController extends Controller
{
    /**
     * Show products and the add-product form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $products = Product::query()
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'description', 'price', 'tax_rate', 'stock_quantity']);

        return view('products.index', [
            'products' => $products,
        ]);
    }

    /**
     * Store a new product with a unique SKU.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        Product::query()->create([
            'name' => $data['name'],
            'sku' => $data['sku'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'tax_rate' => $data['tax_rate'],
            'stock_quantity' => $data['stock_quantity'],
            'is_active' => true,
        ]);

        return redirect('/products')->with('status', 'Product created.');
    }
}
