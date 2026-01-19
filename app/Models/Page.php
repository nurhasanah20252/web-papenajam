<?php

namespace App\Models;

use App\Enums\PageStatus;
use App\Enums\PageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'content',
        'meta',
        'featured_image',
        'status',
        'page_type',
        'author_id',
        'template_id',
        'published_at',
        'view_count',
        'builder_content',
        'version',
        'last_edited_by',
        'is_builder_enabled',
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
            'builder_content' => 'array',
            'meta' => 'array',
            'status' => PageStatus::class,
            'page_type' => PageType::class,
            'published_at' => 'datetime',
            'view_count' => 'integer',
            'version' => 'integer',
            'is_builder_enabled' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the author of the page.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the user who last edited the page.
     */
    public function lastEditedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    /**
     * Get the template used by the page.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(PageTemplate::class, 'template_id');
    }

    /**
     * Get the blocks for this page.
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class, 'page_id')->orderBy('order');
    }

    /**
     * Get the versions for this page.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(PageVersion::class)->orderByDesc('version');
    }

    /**
     * Create a new version snapshot of the current page state.
     */
    public function createVersion(?int $userId = null): PageVersion
    {
        $this->incrementVersion();

        return $this->versions()->create([
            'version' => $this->version,
            'content' => $this->content,
            'builder_content' => $this->builder_content,
            'created_by' => $userId ?? $this->last_edited_by,
        ]);
    }

    /**
     * Restore the page to a specific version.
     */
    public function restoreVersion(int $versionId): bool
    {
        $version = $this->versions()->findOrFail($versionId);

        return $this->update([
            'content' => $version->content,
            'builder_content' => $version->builder_content,
            'version' => $this->version + 1,
        ]);
    }

    /**
     * Scope query for published pages.
     */
    public function scopePublished($query)
    {
        return $query->where('status', PageStatus::Published)
            ->where('published_at', '<=', now());
    }

    /**
     * Scope query for draft pages.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', PageStatus::Draft);
    }

    /**
     * Scope query by page type.
     */
    public function scopeByType($query, PageType $type)
    {
        return $query->where('page_type', $type);
    }

    /**
     * Increment view count.
     */
    public function incrementViews(): void
    {
        $this->increment('view_count');
    }

    /**
     * Check if page is published.
     */
    public function isPublished(): bool
    {
        return $this->status === PageStatus::Published
            && $this->published_at !== null
            && $this->published_at->isPast();
    }

    /**
     * Check if page is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === PageStatus::Draft;
    }

    /**
     * Get the URL for the page.
     */
    public function getUrl(): string
    {
        return '/'.$this->slug;
    }

    /**
     * Get meta description or fallback to excerpt.
     */
    public function getMetaDescription(): ?string
    {
        return $this->meta['description'] ?? $this->excerpt;
    }

    /**
     * Get meta keywords.
     */
    public function getMetaKeywords(): array
    {
        return $this->meta['keywords'] ?? [];
    }

    /**
     * Increment version number.
     */
    public function incrementVersion(): void
    {
        $this->increment('version');
    }

    /**
     * Check if page builder is enabled for this page.
     */
    public function isBuilderEnabled(): bool
    {
        return $this->is_builder_enabled ?? false;
    }

    /**
     * Get builder content or empty array.
     */
    public function getBuilderContent(): array
    {
        return $this->builder_content ?? [];
    }
}
