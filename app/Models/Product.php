<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Product extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'brand_id',
        'supplier_id',
        'product_code',
        'name',
        'slug',
        'sku',
        'description',
        'images',
        'price',
        'sale_price',
        'cost_price',
        'grn_price',
        'quantity',
        'inventory_stock',
        'show_quantity',
        'stock_added_count',
        'stock_added_date',
        'security_stock',
        'is_visible',
        'is_featured',
        'type',
        'condition',
        'warranty_duration',
        'warranty_unit',
        'main_specifications',
        'technical_specifications',
        'published_at',
    ];

    protected $casts = [
        'images' => 'array',
        'main_specifications' => 'array',
        'technical_specifications' => 'array',
        'warranty_duration' => 'integer',
        'warranty_unit' => 'string',
        'is_visible' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'grn_price' => 'decimal:2',
        'published_at' => 'date',
        'stock_added_count' => 'integer',
        'stock_added_date' => 'datetime',
        'inventory_stock' => 'integer',
        'show_quantity' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $product) {
            if (is_null($product->show_quantity) && ! is_null($product->quantity)) {
                $product->show_quantity = (int) $product->quantity;
            } elseif (! is_null($product->show_quantity) && is_null($product->quantity)) {
                $product->quantity = (int) $product->show_quantity;
            }

            if (is_null($product->stock_added_count)) {
                $product->stock_added_count = (int) ($product->show_quantity ?? $product->quantity ?? 0);
            }
            if (is_null($product->stock_added_date)) {
                $product->stock_added_date = now();
            }
        });

        static::saving(function (self $product) {
            if (is_array($product->main_specifications)) {
                $cleaned = [];
                foreach ($product->main_specifications as $k => $v) {
                    if (! is_null($v) && trim((string) $v) !== '') {
                        $cleaned[$k] = trim((string) $v);
                    }
                }
                $product->main_specifications = $cleaned;
            }

            if (is_array($product->technical_specifications)) {
                $product->technical_specifications = array_filter(
                    $product->technical_specifications,
                    fn ($val) => ! is_null($val) && trim((string) $val) !== ''
                );
            }
        });
    }

    // Relationships
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(ProductCollection::class, 'collection_product');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function inventoryProduct(): BelongsTo
    {
        return $this->belongsTo(InventoryProduct::class, 'product_code', 'product_code');
    }

    public function grnItems(): HasMany
    {
        return $this->hasMany(GrnItem::class, 'product_code', 'product_code');
    }

    public function latestGrnItem()
    {
        return $this->hasOne(GrnItem::class, 'product_code', 'product_code')->latestOfMany();
    }

    public function getBalanceInventoryStockAttribute(): int
    {
        $total = $this->product_code
            ? (int) GrnItem::where('product_code', $this->product_code)->sum('product_quantity')
            : (int) ($this->inventory_stock ?? 0);
        $show = (int) ($this->show_quantity ?? $this->quantity ?? 0);

        return max(0, $total - $show);
    }

    // Audit Logs
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'sku', 'product_code', 'price', 'quantity', 'show_quantity', 'inventory_stock', 'is_visible'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
