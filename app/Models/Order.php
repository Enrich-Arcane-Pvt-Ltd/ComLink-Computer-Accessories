<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_secondary_phone',
        'shipping_address_line_1',
        'shipping_address_line_2',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'shipping_country',
        'subtotal',
        'shipping_fee',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'stripe_payment_intent_id',
        'courier_name',
        'tracking_number',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get first item image or placeholder.
     */
    public function getFirstProductImageAttribute(): string
    {
        $firstItem = $this->items->first();
        if ($firstItem && ! empty($firstItem->product_image)) {
            return $firstItem->product_image;
        }

        return '/images/placeholder.png';
    }

    /**
     * Total quantity of items in order.
     */
    public function getTotalUnitsAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    /**
     * Total distinct products in order.
     */
    public function getTotalProductsCountAttribute(): int
    {
        return $this->items->count();
    }

    /**
     * Generate unique sequential order number.
     */
    public static function generateOrderNumber(): string
    {
        $prefix = 'CLK-';
        $latest = static::latest('id')->first();
        $nextNum = $latest ? ($latest->id + 10001) : 10001;

        return $prefix.$nextNum;
    }
}
