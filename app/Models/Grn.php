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
        'grn_type',
        'supplier_id',
        'purchase_order_id',
        'direct_po_number',
        'sub_total',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'sub_total' => 'decimal:2',
        'created_at' => 'datetime',
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

    public function getPoReferenceAttribute(): string
    {
        return $this->purchaseOrder?->po_number
            ?? $this->direct_po_number
            ?? 'Direct';
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

    public static function generateDirectPoNumber(): string
    {
        $year = date('Y');
        $prefix = "DIR-PO-{$year}-";
        $latest = self::where('direct_po_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('direct_po_number');

        if ($latest && preg_match('/'.preg_quote($prefix, '/').'(\d+)/', $latest, $matches)) {
            $next = (int) $matches[1] + 1;
        } else {
            $next = 1;
        }

        return $prefix.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }

    public function recalculateSubTotal(): void
    {
        $total = (float) $this->items()->sum('total');
        if ($total <= 0) {
            $total = (float) $this->items()->sum(\Illuminate\Support\Facades\DB::raw('COALESCE(price_per_unit, 0) * COALESCE(product_quantity, 1)'));
        }
        $this->sub_total = round($total, 2);
        $this->save();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['grn_number', 'grn_type', 'supplier_id', 'purchase_order_id', 'direct_po_number', 'sub_total'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
