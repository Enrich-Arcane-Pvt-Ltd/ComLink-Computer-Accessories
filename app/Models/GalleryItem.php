<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class GalleryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image',
        'external_link',
        'is_visible',
        'is_featured_home',
        'sort_order',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'is_featured_home' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? Storage::url($this->image)
            : url('https://placehold.co/600x600?text=No+Image');
    }
}
