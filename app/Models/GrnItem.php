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
        'stock_code',
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

    public function getStockCodeAttribute(): string
    {
        if (! empty($this->attributes['stock_code'])) {
            return $this->attributes['stock_code'];
        }

        return static::generateStockCode($this->product_code, $this->color_name, $this->id);
    }

    public static function generateStockCode(?string $productCode, ?string $colorName = null, ?int $excludeId = null): string
    {
        $prefix = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) $productCode));
        if (empty($prefix)) {
            $prefix = 'ITEM';
        }

        $colorPart = ! empty($colorName)
            ? '-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $colorName), 0, 3))
            : '';

        $query = static::where('product_code', $productCode);
        if ($colorName) {
            $query->where('color_name', $colorName);
        }
        if ($excludeId) {
            $query->where('id', '<=', $excludeId);
        }

        $batchIndex = max(1, $query->count());
        $batchNum = str_pad((string) $batchIndex, 3, '0', STR_PAD_LEFT);

        return "STK-{$prefix}{$colorPart}-{$batchNum}";
    }

    protected static function booted(): void
    {
        static::saving(function (GrnItem $item) {
            $item->total = round((float) $item->price_per_unit * (int) $item->product_quantity, 2);

            if (empty($item->attributes['stock_code']) && \Illuminate\Support\Facades\Schema::hasColumn('grn_items', 'stock_code')) {
                $item->stock_code = static::generateStockCode($item->product_code, $item->color_name, $item->id);
            }
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

        $latestGrnItem = static::with('grn')->where('product_code', $productCode)->latest('id')->first();
        $latestSupplierId = $latestGrnItem?->grn?->supplier_id;

        if ($latestSupplierId) {
            InventoryProduct::where('product_code', $productCode)->update([
                'supplier_id' => $latestSupplierId,
            ]);
        }

        // 1. Sync specific color variant if applicable
        if ($productVariantId) {
            $variant = ProductVariant::find($productVariantId);
            if ($variant) {
                $variantStock = (int) static::where('product_variant_id', $productVariantId)->sum('product_quantity');
                $variantLatestPrice = static::where('product_variant_id', $productVariantId)->latest('id')->value('price_per_unit');
                $vSold = (int) OrderItem::where('variant_id', $variant->id)
                    ->whereHas('order', fn ($q) => $q->where('payment_status', 'Paid'))
                    ->sum('quantity');
                $vAvail = max(0, $variantStock - $vSold);

                $variantUpdate = [
                    'inventory_stock' => $variantStock,
                    'show_quantity'   => $variantStock,
                    'stock'           => $vAvail,
                ];
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
                    $vSold = (int) OrderItem::where('variant_id', $variant->id)
                        ->whereHas('order', fn ($q) => $q->where('payment_status', 'Paid'))
                        ->sum('quantity');
                    $vAvail = max(0, $variantStock - $vSold);

                    $variantUpdate = [
                        'inventory_stock' => $variantStock,
                        'show_quantity'   => $variantStock,
                        'stock'           => $vAvail,
                    ];
                    if ($variantLatestPrice !== null) {
                        $variantUpdate['grn_price'] = $variantLatestPrice;
                    }
                    $variant->update($variantUpdate);
                }
            }
        }

        // 2. Sync parent product total
        $totalStock = (int) static::where('product_code', $productCode)->sum('product_quantity');
        $latestPrice = $latestGrnItem?->price_per_unit;

        $parent = Product::where('product_code', $productCode)->first();
        if ($parent) {
            $sold = (int) $parent->sold_count;
            $avail = max(0, $totalStock - $sold);

            $updates = [
                'inventory_stock' => $totalStock,
                'show_quantity'   => $totalStock,
                'quantity'        => $avail,
            ];
            if ($latestPrice !== null) {
                $updates['grn_price'] = $latestPrice;
                $updates['cost_price'] = $latestPrice;
            }
            if ($latestSupplierId) {
                $updates['supplier_id'] = $latestSupplierId;
            }
            $parent->update($updates);
        }
    }
}
