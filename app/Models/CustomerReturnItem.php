<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_return_id',
        'order_item_id',
        'product_id',
        'variant_id',
        'product_name',
        'quantity',
        'unit_price',
        'refund_subtotal',
        'unit_cost',
        'condition',
        'resolution_status',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'refund_subtotal' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function customerReturn(): BelongsTo
    {
        return $this->belongsTo(CustomerReturn::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function supplierReturnItems(): HasMany
    {
        return $this->hasMany(SupplierReturnItem::class);
    }

    public function getReturnedToSupplierQuantityAttribute(): int
    {
        return (int) $this->supplierReturnItems()
            ->whereHas('supplierReturn', fn ($q) => $q->where('status', '!=', 'rejected'))
            ->sum('quantity');
    }

    public function getRemainingDamagedQuantityAttribute(): int
    {
        if ($this->condition !== 'damaged' || $this->resolution_status === 'scrapped') {
            return 0;
        }

        return max(0, (int) $this->quantity - $this->returned_to_supplier_quantity);
    }
}
