<?php

namespace App\Models;

use App\Enums\NewsStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
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
        'tags',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'content' => 'array',
            'tags' => 'array',
            'is_featured' => 'boolean',
            'views_count' => 'integer',
            'status' => NewsStatus::class,
            'published_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the category of the news.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Get the author of the news.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Scope query for published news.
     */
    public function scopePublished($query)
    {
        return $query->where('status', NewsStatus::Published)
            ->where('published_at', '<=', now());
    }

    /**
     * Scope query for featured news.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope query by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope query with tags.
     */
    public function scopeWithTag($query, string $tag)
    {
        return $query->whereRaw('JSON_CONTAINS(tags, ?)', [json_encode($tag)]);
    }

    /**
     * Increment view count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Check if news is published.
     */
    public function isPublished(): bool
    {
        return $this->status === NewsStatus::Published
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    /**
     * Get the URL for the news article.
     */
    public function getUrl(): string
    {
        return '/news/'.$this->slug;
    }

    /**
     * Get meta description or fallback to excerpt.
     */
    public function getMetaDescription(): ?string
    {
        return $this->excerpt;
    }
}
