<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'sku',
        'description',
        'price',
        'tax_rate',
        'stock_quantity',
        'is_active',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'stock_quantity' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Limit the query to products below the configured low-stock threshold.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int|null  $threshold
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLowStock($query, $threshold = null)
    {
        $threshold = $threshold !== null
            ? (int) $threshold
            : (int) config('inventory.low_stock_threshold', 10);

        return $query->where('stock_quantity', '<', $threshold);
    }
}
