<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'image',
        'author',
        'author_intro',
        'category',
        'tags',
        'is_active',
        'views_count',
        'likes_count',
    ];

    protected $casts = [
        'tags'      => 'array',
        'is_active' => 'boolean',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? Storage::url($this->image)
            : url('https://placehold.co/800x600?text=No+Image');
    }

    /**
     * Public-facing comments — approved only, latest first.
     * BlogController show() method මෙය use කරයි.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class)
            ->where('is_approved', true)
            ->latest();
    }

    /**
     * All comments including unapproved — Filament RelationManager use කරයි.
     * Admin panel එකෙන් approve/reject කරන්න.
     */
    public function allComments(): HasMany
    {
        return $this->hasMany(BlogComment::class)->latest();
    }


    public function likedByUsers()
    {
        return $this->belongsToMany(User::class, 'blog_post_likes', 'post_id', 'user_id')->withTimestamps();
    }
}
