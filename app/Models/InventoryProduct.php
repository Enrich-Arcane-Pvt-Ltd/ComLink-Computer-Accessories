<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InventoryProduct extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'supplier_id',
        'product_code',
        'product_name',
        'product_image',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function grnItems(): HasMany
    {
        return $this->hasMany(GrnItem::class, 'product_code', 'product_code');
    }

    public function storefrontProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_code', 'product_code');
    }

    public function getTotalStockAttribute(): int
    {
        return (int) $this->grnItems()->sum('product_quantity');
    }

    public function getSoldCountAttribute(): int
    {
        $product = $this->storefrontProduct;
        if ($product) {
            return (int) $product->sold_count;
        }

        return (int) OrderItem::where('product_name', $this->product_name)
            ->whereHas('order', fn ($q) => $q->where('payment_status', 'Paid'))
            ->sum('quantity');
    }

    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->total_stock - $this->sold_count);
    }

    public function getLatestPriceAttribute(): ?float
    {
        $storeProduct = $this->storefrontProduct;
        if ($storeProduct) {
            return (float) ($storeProduct->sale_price ?: $storeProduct->price ?: 0);
        }

        $latestGrn = $this->grnItems()->latest('id')->first();

        return $latestGrn ? (float) $latestGrn->price_per_unit : null;
    }

    public function getLatestGrnPriceAttribute(): ?float
    {
        $latestGrn = $this->grnItems()->latest('id')->first();

        return $latestGrn ? (float) $latestGrn->price_per_unit : null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['supplier_id', 'product_code', 'product_name', 'product_image'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
