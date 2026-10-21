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
        'product_variant_id',
        'product_code',
        'product_name',
        'color_name',
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

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    protected static function booted(): void
    {
        static::saving(function (GrnItem $item) {
            $item->total = round((float) $item->price_per_unit * (int) $item->product_quantity, 2);
        });

        static::saved(function (GrnItem $item) {
            $item->grn?->recalculateSubTotal();
            static::syncProductInventory($item->product_code, $item->product_variant_id, $item->color_name);
        });

        static::deleted(function (GrnItem $item) {
            $item->grn?->recalculateSubTotal();
            static::syncProductInventory($item->product_code, $item->product_variant_id, $item->color_name);
        });
    }

    public static function syncProductInventory(?string $productCode, ?int $productVariantId = null, ?string $colorName = null): void
    {
        if (! $productCode) {
            return;
        }

        // 1. Sync specific color variant if applicable
        if ($productVariantId) {
            $variant = ProductVariant::find($productVariantId);
            if ($variant) {
                $variantStock = (int) static::where('product_variant_id', $productVariantId)->sum('product_quantity');
                $variantLatestPrice = static::where('product_variant_id', $productVariantId)->latest('id')->value('price_per_unit');

                $variantUpdate = ['inventory_stock' => $variantStock];
                if ($variantLatestPrice !== null) {
                    $variantUpdate['grn_price'] = $variantLatestPrice;
                }
                $variant->update($variantUpdate);
            }
        } elseif ($colorName) {
            $product = Product::where('product_code', $productCode)->first();
            if ($product) {
                $variant = $product->variants()->where('color_name', $colorName)->first();
                if ($variant) {
                    $variantStock = (int) static::where('product_code', $productCode)
                        ->where('color_name', $colorName)
                        ->sum('product_quantity');
                    $variantLatestPrice = static::where('product_code', $productCode)
                        ->where('color_name', $colorName)
                        ->latest('id')
                        ->value('price_per_unit');

                    $variantUpdate = ['inventory_stock' => $variantStock];
                    if ($variantLatestPrice !== null) {
                        $variantUpdate['grn_price'] = $variantLatestPrice;
                    }
                    $variant->update($variantUpdate);
                }
            }
        }

        // 2. Sync parent product total
        $totalStock = (int) static::where('product_code', $productCode)->sum('product_quantity');
        $latestPrice = static::where('product_code', $productCode)->latest('id')->value('price_per_unit');

        $parent = Product::where('product_code', $productCode)->first();
        if ($parent) {
            $updates = [
                'inventory_stock' => $totalStock,
            ];
            if ($latestPrice !== null) {
                $updates['grn_price'] = $latestPrice;
            }
            $parent->update($updates);
        }
    }
}
