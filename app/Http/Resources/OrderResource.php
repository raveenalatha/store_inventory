<?php

namespace App\Http\Resources;

use App\Support\AppDate;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'subtotal' => (float) $this->subtotal,
            'tax_amount' => (float) $this->tax_amount,
            'total_amount' => (float) $this->total_amount,
            'amount_given' => $this->amount_given !== null ? (float) $this->amount_given : null,
            'change_due' => $this->change_due !== null ? (float) $this->change_due : null,
            'created_at' => AppDate::display($this->created_at),
            'customer' => [
                'id' => optional($this->customer)->id,
                'name' => optional($this->customer)->name,
                'email' => optional($this->customer)->email,
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
