<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        if ($request->routeIs('products.low-stock')) {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'sku' => $this->sku,
                'stock_quantity' => (int) $this->stock_quantity,
            ];
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => $this->description,
            'price' => (float) $this->price,
            'tax_rate' => (float) $this->tax_rate,
            'stock_quantity' => (int) $this->stock_quantity,
            'is_active' => (bool) $this->is_active,
        ];
    }
}
