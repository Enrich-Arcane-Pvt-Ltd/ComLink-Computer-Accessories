<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'color_name',
        'variant_name',
        'attributes',
        'sku',
        'condition',
        'price',
        'sale_price',
        'grn_price',
        'discount_percentage',
        'stock',
        'inventory_stock',
        'show_quantity',
        'image',
    ];

    protected function casts(): array
    {
        return [
            'attributes' => 'array',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'grn_price' => 'decimal:2',
            'discount_percentage' => 'integer',
            'stock' => 'integer',
            'inventory_stock' => 'integer',
            'show_quantity' => 'integer',
        ];
    }

    public function setImageAttribute($value): void
    {
        if (is_array($value)) {
            $filtered = array_values(array_filter($value));
            $value = $filtered[0] ?? null;
        }

        $this->attributes['image'] = is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Display name for storefront and invoices.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->variant_name) {
            return $this->variant_name;
        }

        $baseName = $this->product?->name ?? 'Product';

        return $this->color_name ? "{$baseName} - {$this->color_name}" : $baseName;
    }

    /**
     * Calculated effective selling price.
     */
    public function getEffectivePriceAttribute(): float
    {
        if ($this->sale_price !== null && (float) $this->sale_price > 0) {
            return (float) $this->sale_price;
        }

        return (float) ($this->price ?? 0);
    }
}
