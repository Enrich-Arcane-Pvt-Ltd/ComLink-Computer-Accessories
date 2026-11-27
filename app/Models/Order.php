<?php

namespace App\Models;

use App\Mail\OrderStatusUpdatedMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'is_guest',
        'guest_token',
        'status',
        'delivered_at',
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
        'refund_status',
        'refunded_amount',
        'stripe_payment_intent_id',
        'courier_name',
        'tracking_number',
        'notes',
    ];

    protected $casts = [
        'is_guest' => 'boolean',
        'delivered_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saving(function (Order $order): void {
            if ($order->isDirty('status') && $order->status === 'Delivered' && empty($order->delivered_at)) {
                $order->delivered_at = now();
            }
        });

        static::updated(function (Order $order): void {
            if ($order->wasChanged('status') && ! empty($order->customer_email)) {
                $previousStatus = (string) ($order->getOriginal('status') ?? 'Processing');
                $newStatus = (string) $order->status;

                try {
                    Mail::to($order->customer_email)->send(new OrderStatusUpdatedMail($order, $previousStatus, $newStatus));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        });
    }

    public function getOrderAccessUrlAttribute(): string
    {
        if ($this->is_guest && ! empty($this->guest_token)) {
            return route('guest.orders.show', ['token' => $this->guest_token]);
        }

        return route('orders.index');
    }

    public function getReceiptUrl(bool $preview = false): string
    {
        $params = $preview ? ['preview' => 1] : [];

        if ($this->is_guest && ! empty($this->guest_token)) {
            return route('guest.orders.receipt', array_merge(['token' => $this->guest_token], $params));
        }

        return route('orders.receipt', array_merge(['order' => $this->id], $params));
    }

    public function getRefundDeadlineAttribute(): ?\Illuminate\Support\Carbon
    {
        if ($this->status !== 'Delivered') {
            return null;
        }

        $deliveredAt = $this->delivered_at ?? $this->updated_at;

        return $deliveredAt ? $deliveredAt->copy()->addDays(7) : null;
    }

    public function isWithinRefundWindow(): bool
    {
        $deadline = $this->refund_deadline;

        return $deadline !== null && now()->lessThanOrEqualTo($deadline);
    }

    public function canAcceptRefundRequest(): bool
    {
        return $this->payment_status === 'Paid'
            && ($this->refund_status ?? 'none') !== 'full'
            && $this->isWithinRefundWindow()
            && $this->items->contains(fn ($item) => $item->returnable_quantity > 0);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customerReturns(): HasMany
    {
        return $this->hasMany(CustomerReturn::class);
    }

    public function getNetAmountAttribute(): float
    {
        return max(0.0, (float) $this->total_amount - (float) ($this->refunded_amount ?? 0));
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
