<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * SubCategory Model
 * Represents a child category with a parent category.
 */
class SubCategory extends Category
{
    /**
     * Use the shared categories table
     */
    protected $table = 'categories';

    /**
     * The "booted" method of the model.
     * Applies a global scope to only query categories that have a parent_id.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('subcategory', function (Builder $builder) {
            $builder->whereNotNull('parent_id');
        });

        // When creating through SubCategory, ensure parent_id is tracked
        static::creating(function ($subCategory) {
            if (empty($subCategory->sort_order)) {
                $subCategory->sort_order = 0;
            }
        });
    }

    /**
     * Parent Category relationship
     */
    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }
}
