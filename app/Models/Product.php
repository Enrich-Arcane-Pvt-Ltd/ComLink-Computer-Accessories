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
        'name',
        'slug',
        'sku',
        'description',
        'images',
        'price',
        'sale_price',
        'cost_price',
        'quantity',
        'security_stock',
        'is_visible',
        'is_featured',
        'type',
        'condition',
        'main_specifications',
        'technical_specifications',
        'published_at',
    ];

    protected $casts = [
        'images' => 'array',
        'main_specifications' => 'array',
        'technical_specifications' => 'array',
        'is_visible' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'published_at' => 'date',
    ];

    // Relationships
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
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

    protected static function booted(): void
    {
        static::saving(function ($product) {
            if (is_array($product->main_specifications)) {
                $product->main_specifications = array_filter(
                    $product->main_specifications,
                    fn ($val) => ! is_null($val) && trim((string) $val) !== ''
                );
            }

            if (is_array($product->technical_specifications)) {
                $product->technical_specifications = array_filter(
                    $product->technical_specifications,
                    fn ($val) => ! is_null($val) && trim((string) $val) !== ''
                );
            }
        });
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
