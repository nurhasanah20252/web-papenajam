<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'is_featured',
        'views_count',
        'category_id',
        'author_id',
        'status',
        'published_at',
    ];

    protected $casts = [
        'content' => 'array',
        'is_featured' => 'boolean',
        'views_count' => 'integer',
        'published_at' => 'datetime',
    ];

    /**
     * Get the author.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
