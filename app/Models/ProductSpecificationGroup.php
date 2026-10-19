<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSpecificationGroup extends Model
{
    use HasFactory;

    protected $table = 'product_specification_groups';

    protected $fillable = [
        'category_id',
        'sub_category_id',
        'main_specs',
        'general_specs',
        'performance_specs',
        'compatibility_specs',
    ];

    protected $casts = [
        'main_specs' => 'array',
        'general_specs' => 'array',
        'performance_specs' => 'array',
        'compatibility_specs' => 'array',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $group) {
            if (is_array($group->main_specs)) {
                $group->main_specs = array_values(array_filter($group->main_specs, fn ($v) => trim((string) $v) !== ''));
            }
            if (is_array($group->general_specs)) {
                $group->general_specs = array_values(array_filter($group->general_specs, fn ($v) => trim((string) $v) !== ''));
            }
            if (is_array($group->performance_specs)) {
                $group->performance_specs = array_values(array_filter($group->performance_specs, fn ($v) => trim((string) $v) !== ''));
            }
            if (is_array($group->compatibility_specs)) {
                $group->compatibility_specs = array_values(array_filter($group->compatibility_specs, fn ($v) => trim((string) $v) !== ''));
            }
        });

        static::saved(function (self $group) {
            $group->syncProductSpecifications();
        });

        static::deleted(function (self $group) {
            $query = ProductSpecification::where('category_id', $group->category_id);
            if ($group->sub_category_id) {
                $query->where('sub_category_id', $group->sub_category_id);
            } else {
                $query->whereNull('sub_category_id');
            }
            $query->delete();
        });
    }

    /**
     * Parent category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Optional sub category
     */
    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    /**
     * Synchronize with the product_specifications table
     */
    public function syncProductSpecifications(): void
    {
        $query = ProductSpecification::where('category_id', $this->category_id);
        if ($this->sub_category_id) {
            $query->where('sub_category_id', $this->sub_category_id);
        } else {
            $query->whereNull('sub_category_id');
        }
        $query->delete();

        if (is_array($this->main_specs)) {
            foreach (array_values(array_filter($this->main_specs)) as $order => $title) {
                if (trim((string) $title) === '') continue;
                ProductSpecification::create([
                    'category_id' => $this->category_id,
                    'sub_category_id' => $this->sub_category_id,
                    'specification_type' => 'main',
                    'sub_section' => null,
                    'title' => trim((string) $title),
                    'sort_order' => $order + 1,
                ]);
            }
        }

        if (is_array($this->general_specs)) {
            foreach (array_values(array_filter($this->general_specs)) as $order => $title) {
                if (trim((string) $title) === '') continue;
                ProductSpecification::create([
                    'category_id' => $this->category_id,
                    'sub_category_id' => $this->sub_category_id,
                    'specification_type' => 'technical',
                    'sub_section' => 'general',
                    'title' => trim((string) $title),
                    'sort_order' => $order + 1,
                ]);
            }
        }

        if (is_array($this->performance_specs)) {
            foreach (array_values(array_filter($this->performance_specs)) as $order => $title) {
                if (trim((string) $title) === '') continue;
                ProductSpecification::create([
                    'category_id' => $this->category_id,
                    'sub_category_id' => $this->sub_category_id,
                    'specification_type' => 'technical',
                    'sub_section' => 'performance',
                    'title' => trim((string) $title),
                    'sort_order' => $order + 1,
                ]);
            }
        }

        if (is_array($this->compatibility_specs)) {
            foreach (array_values(array_filter($this->compatibility_specs)) as $order => $title) {
                if (trim((string) $title) === '') continue;
                ProductSpecification::create([
                    'category_id' => $this->category_id,
                    'sub_category_id' => $this->sub_category_id,
                    'specification_type' => 'technical',
                    'sub_section' => 'compatibility',
                    'title' => trim((string) $title),
                    'sort_order' => $order + 1,
                ]);
            }
        }
    }
}
