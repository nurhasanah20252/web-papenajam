<?php

namespace App\Services\JoomlaMigration;

use App\Models\News;
use Illuminate\Support\Str;

class NewsMigrationService extends BaseMigrationService
{
    public function getType(): string
    {
        return JoomlaMigration::TYPE_NEWS;
    }

    public function getModelClass(): string
    {
        return News::class;
    }

    public function validateData(array $data): bool
    {
        return !empty($data['title'] ?? $data['articlename'] ?? null);
    }

    public function transformData(array $data): array
    {
        $title = $data['title'] ?? $data['articlename'] ?? '';
        $alias = $data['alias'] ?? Str::slug($title);

        // Clean content
        $fullContent = ($data['introtext'] ?? '').' '.($data['fulltext'] ?? '');
        $cleanedContent = $this->cleanContent($fullContent);

        return [
            'title' => $title,
            'slug' => $this->generateSlug($alias),
            'excerpt' => $this->cleanExcerpt($data['introtext'] ?? ''),
            'content' => $cleanedContent['content'],
            'featured_image' => $this->processFeaturedImage($data['images'] ?? []),
            'is_featured' => $this->isFeatured($data),
            'views_count' => $data['hits'] ?? 0,
            'category_id' => $this->mapCategory($data['catid'] ?? null),
            'author_id' => $this->mapAuthor($data['created_by'] ?? null),
            'status' => $this->mapStatus($data['state'] ?? 0),
            'published_at' => $this->mapPublishedDate($data, $data['state'] ?? 0),
        ];
    }

    public function saveData(array $data): int
    {
        $news = News::create($data);

        return $news->id;
    }

    /**
     * Map Joomla article status to Laravel status.
     */
    protected function mapStatus(int $joomlaState): string
    {
        return match ($joomlaState) {
            1 => 'published',
            -1 => 'archived',
            default => 'draft',
        };
    }

    /**
     * Map published date.
     */
    protected function mapPublishedDate(array $data, int $state): ?string
    {
        if ($state !== 1) {
            return null;
        }

        return $data['publish_up'] ?? $data['created'] ?? now()->format('Y-m-d H:i:s');
    }

    /**
     * Clean and create excerpt.
     */
    protected function cleanExcerpt(string $introtext): ?string
    {
        if (empty($introtext)) {
            return null;
        }

        $excerpt = strip_tags($introtext);
        $excerpt = trim($excerpt);

        if (strlen($excerpt) > 500) {
            $excerpt = substr($excerpt, 0, 497).'...';
        }

        return $excerpt;
    }

    /**
     * Process featured image from Joomla images array.
     */
    protected function processFeaturedImage(array $images): ?string
    {
        if (isset($images['image_intro']) && !empty($images['image_intro'])) {
            return $this->cleanImagePath($images['image_intro']);
        }

        if (isset($images['image_fulltext']) && !empty($images['image_fulltext'])) {
            return $this->cleanImagePath($images['image_fulltext']);
        }

        return null;
    }

    /**
     * Clean image path.
     */
    protected function cleanImagePath(string $path): string
    {
        $path = preg_replace('#^/images/#', 'storage/', $path);
        $path = preg_replace('#^images/#', 'storage/', $path);

        return $path;
    }

    /**
     * Map Joomla category to local category.
     */
    protected function mapCategory(?int $joomlaCategoryId): ?int
    {
        if ($joomlaCategoryId === null) {
            return null;
        }

        $item = JoomlaMigrationItem::where('migration_id', $this->migration->id)
            ->where('type', JoomlaMigration::TYPE_CATEGORIES)
            ->where('joomla_id', $joomlaCategoryId)
            ->where('status', JoomlaMigrationItem::STATUS_COMPLETED)
            ->first();

        return $item?->local_id;
    }

    /**
     * Map Joomla author to local author.
     */
    protected function mapAuthor(?int $joomlaAuthorId): ?int
    {
        if ($joomlaAuthorId === null) {
            return null;
        }

        $item = JoomlaMigrationItem::where('migration_id', $this->migration->id)
            ->where('type', 'users')
            ->where('joomla_id', $joomlaAuthorId)
            ->where('status', JoomlaMigrationItem::STATUS_COMPLETED)
            ->first();

        return $item?->local_id;
    }

    /**
     * Check if article is featured.
     */
    protected function isFeatured(array $data): bool
    {
        return ($data['featured'] ?? $data['frontpage'] ?? 0) == 1;
    }
}
