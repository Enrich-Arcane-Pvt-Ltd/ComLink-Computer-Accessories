<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'product_name',
        'color',
        'product_image',
        'category_name',
        'unit_price',
        'quantity',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'quantity' => 'integer',
            'total_price' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function customerReturnItems(): HasMany
    {
        return $this->hasMany(CustomerReturnItem::class);
    }

    public function getReturnedQuantityAttribute(): int
    {
        return (int) $this->customerReturnItems()
            ->whereHas('customerReturn', fn ($q) => $q->whereIn('status', ['approved', 'pending']))
            ->sum('quantity');
    }

    public function getReturnableQuantityAttribute(): int
    {
        return max(0, (int) $this->quantity - $this->returned_quantity);
    }
}
