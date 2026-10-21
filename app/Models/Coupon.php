<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Activitylog\LogOptions; // 1. මෙය Import කරන්න
use Spatie\Activitylog\Traits\LogsActivity; // 2. මෙයත් Import කරන්න

class Coupon extends Model
{
    use HasFactory, LogsActivity; // 3. Trait එක මෙතනට දාන්න

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_spend',
        'usage_limit',
        'usage_limit_per_user',
        'used_count',
        'starts_at',
        'ends_at',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
        'value' => 'decimal:2',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(ProductCollection::class, 'coupon_product_collection', 'coupon_id', 'product_collection_id');
    }

    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return false;
        }
        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }
        if ($this->ends_at && now()->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    // 4. Audit Log Options එකතු කරන්න (මේකෙන් තමයි ලොග් වෙන්න ඕන දේවල් තීරණය කරන්නේ)
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'code',
                'type',
                'value',
                'min_spend',
                'usage_limit',
                'is_active',
                'starts_at',
                'ends_at',
            ]) // ලොග් කරන්න අවශ්‍ය Columns ටික
            ->logOnlyDirty() // වෙනස් වුන දේවල් විතරක් ලොග් කරන්න
            ->dontSubmitEmptyLogs(); // කිසි දෙයක් වෙනස් වුනේ නැත්නම් ලොග් කරන්න එපා
    }
}
