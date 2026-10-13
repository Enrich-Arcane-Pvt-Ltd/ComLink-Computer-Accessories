<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogComment extends Model
{
    protected $fillable = ['post_id', 'user_name', 'user_email', 'comment', 'is_approved'];
}
