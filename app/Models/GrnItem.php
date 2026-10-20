<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'grn_id',
        'inventory_product_id',
        'product_code',
        'product_name',
        'product_condition',
        'price_per_unit',
        'product_quantity',
        'total',
    ];

    protected $casts = [
        'price_per_unit' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function grn(): BelongsTo
    {
        return $this->belongsTo(Grn::class);
    }

    public function inventoryProduct(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class);
    }

    protected static function booted(): void
    {
        static::saving(function (GrnItem $item) {
            $item->total = round((float) $item->price_per_unit * (int) $item->product_quantity, 2);
        });

        static::saved(function (GrnItem $item) {
            $item->grn?->recalculateSubTotal();
        });

        static::deleted(function (GrnItem $item) {
            $item->grn?->recalculateSubTotal();
        });
    }
}
