<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Grn extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'grn_number',
        'supplier_id',
        'purchase_order_id',
        'sub_total',
        'notes',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(GrnItem::class);
    }

    public static function generateGrnNumber(): string
    {
        $year = date('Y');
        $prefix = "COM-GRN-{$year}INV-";
        $latest = self::where('grn_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('grn_number');

        if ($latest && preg_match('/'.preg_quote($prefix, '/').'(\d+)/', $latest, $matches)) {
            $next = (int) $matches[1] + 1;
        } else {
            $next = 1;
        }

        return $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function recalculateSubTotal(): void
    {
        $this->sub_total = $this->items()->sum('total');
        $this->save();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['grn_number', 'supplier_id', 'purchase_order_id', 'sub_total'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
