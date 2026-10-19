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
        'name',
        'slug',
        'sku',
        'description',
        'images',
        'price',
        'sale_price',
        'cost_price',
        'quantity',
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
        'published_at' => 'date',
        'stock_added_count' => 'integer',
        'stock_added_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $product) {
            if (is_null($product->stock_added_count)) {
                $product->stock_added_count = (int) ($product->quantity ?? 0);
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

    // Audit Logs
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'sku', 'price', 'quantity', 'is_visible'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
