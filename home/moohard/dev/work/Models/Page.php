<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'content',
        'meta',
        'featured_image',
        'status',
        'author_id',
        'template_id',
        'published_at',
    ];

    protected $casts = [
        'content' => 'array',
        'meta' => 'array',
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
     * Get the template.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(PageTemplate::class, 'template_id');
    }

    /**
     * Get blocks for this page.
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->orderBy('order');
    }
}
