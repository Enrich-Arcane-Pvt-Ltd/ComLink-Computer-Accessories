<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustomerReturn extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'return_number',
        'order_id',
        'user_id',
        'refund_amount',
        'refund_method',
        'status',
        'requested_by_customer',
        'reason',
        'admin_notes',
        'payment_receipt_path',
        'evidence_image_path',
        'bank_name',
        'bank_branch',
        'account_number',
        'account_name',
        'rejection_reason',
        'refunded_at',
        'created_by',
    ];

    protected $casts = [
        'refund_amount' => 'decimal:2',
        'requested_by_customer' => 'boolean',
        'refunded_at' => 'datetime',
    ];

    public function getPaymentReceiptUrlAttribute(): ?string
    {
        return $this->payment_receipt_path ? asset('storage/' . $this->payment_receipt_path) : null;
    }

    public function getEvidenceImageUrlAttribute(): ?string
    {
        return $this->evidence_image_path ? asset('storage/' . $this->evidence_image_path) : null;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['return_number', 'order_id', 'refund_amount', 'refund_method', 'status', 'reason'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomerReturnItem::class);
    }

    public static function generateReturnNumber(): string
    {
        $year = date('Y');
        $prefix = "COM-RET-{$year}-";
        $latest = self::where('return_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('return_number');

        if ($latest && preg_match('/' . preg_quote($prefix, '/') . '(\d+)/', $latest, $matches)) {
            $next = (int) $matches[1] + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
