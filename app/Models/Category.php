<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Category extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'parent_id',
        'image',
        'description',
        'is_visible',
        'sort_order',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['image_url'];

    /**
     * Parent category relationship
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Direct alias for parent
     */
    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Child categories (subcategories)
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order', 'asc');
    }

    /**
     * Semantic alias for children
     */
    public function subcategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order', 'asc');
    }

    /**
     * Products in this category
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    /**
     * Scope: Only top-level main categories
     */
    public function scopeMain(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope: Only subcategories
     */
    public function scopeSub(Builder $query): Builder
    {
        return $query->whereNotNull('parent_id');
    }

    /**
     * Scope: Only visible categories
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    /**
     * Helper: Check if this is a top-level category
     */
    public function isMainCategory(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Helper: Check if this is a subcategory
     */
    public function isSubcategory(): bool
    {
        return ! is_null($this->parent_id);
    }

    /**
     * Accessor: Compute correct public image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            // If already full URL or starts with slash
            if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
                return $this->image;
            }

            if (str_starts_with($this->image, '/')) {
                return url($this->image);
            }

            // Check if file exists directly in public/ (e.g. images/categories/...)
            if (file_exists(public_path($this->image))) {
                return url($this->image);
            }

            // Fallback to storage URL (e.g. categories/filename.jpg)
            return Storage::url($this->image);
        }

        // Check if there is an image with matching slug in images/categories
        foreach (['webp', 'png', 'jpg', 'jpeg'] as $ext) {
            $slugImg = "images/categories/{$this->slug}.{$ext}";
            if (file_exists(public_path($slugImg))) {
                return url($slugImg);
            }
        }

        return url('/images/placeholder.png');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'slug', 'parent_id', 'is_visible'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
