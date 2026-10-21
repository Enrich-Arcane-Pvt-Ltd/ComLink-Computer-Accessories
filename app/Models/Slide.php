<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Slide extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',
        'image',
        'title',
        'subtitle',
        'description',
        'button_text',
        'button_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = ['image_url'];

    // Image URL එක සකස් කරන Accessor එක
    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? Storage::url($this->image)
            : url('https://placehold.co/1920x1080?text=No+Image');
    }
}
