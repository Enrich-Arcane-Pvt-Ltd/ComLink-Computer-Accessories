<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrnReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_number',
        'title',
        'analysis_type',
        'supplier_id',
        'product_code',
        'date_from',
        'date_to',
        'total_grns',
        'total_items_count',
        'total_quantity',
        'total_amount',
        'report_data',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'total_grns' => 'integer',
        'total_items_count' => 'integer',
        'total_quantity' => 'integer',
        'total_amount' => 'decimal:2',
        'report_data' => 'array',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::creating(function (GrnReport $report) {
            if (empty($report->report_number)) {
                $count = static::whereDate('created_at', today())->count() + 1;
                $report->report_number = 'GRN-REP-'.date('Ymd').'-'.str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}
