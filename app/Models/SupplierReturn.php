<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SupplierReturn extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'rma_number',
        'supplier_id',
        'grn_id',
        'status',
        'resolution_type',
        'total_cost_value',
        'credit_amount',
        'dispatched_at',
        'resolved_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'total_cost_value' => 'decimal:2',
        'credit_amount' => 'decimal:2',
        'dispatched_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['rma_number', 'supplier_id', 'grn_id', 'status', 'resolution_type', 'total_cost_value', 'credit_amount'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function grn(): BelongsTo
    {
        return $this->belongsTo(Grn::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierReturnItem::class);
    }

    public static function generateRmaNumber(): string
    {
        $year = date('Y');
        $prefix = "COM-SRN-{$year}-";
        $latest = self::where('rma_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->value('rma_number');

        if ($latest && preg_match('/' . preg_quote($prefix, '/') . '(\d+)/', $latest, $matches)) {
            $next = (int) $matches[1] + 1;
        } else {
            $next = 1;
        }

        return $prefix . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }
}
