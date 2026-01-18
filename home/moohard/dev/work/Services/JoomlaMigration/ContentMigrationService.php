<?php

namespace App\Services\JoomlaMigration;

use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Support\Str;

class ContentMigrationService extends BaseMigrationService
{
    public function getType(): string
    {
        return JoomlaMigration::TYPE_PAGES;
    }

    public function getModelClass(): string
    {
        return Page::class;
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
            'meta' => $cleanedContent['meta'],
            'featured_image' => $this->processFeaturedImage($data['images'] ?? []),
            'status' => $this->mapStatus($data['state'] ?? 0),
            'author_id' => $this->mapAuthor($data['created_by'] ?? null),
            'template_id' => null,
            'published_at' => $this->mapPublishedDate($data, $data['state'] ?? 0),
        ];
    }

    public function saveData(array $data): int
    {
        $page = Page::create($data);

        // Create page blocks if content has sections
        $this->createPageBlocks($page, $data);

        return $page->id;
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
     * Map Joomla author to local author.
     */
    protected function mapAuthor(?int $joomlaAuthorId): ?int
    {
        if ($joomlaAuthorId === null) {
            return null;
        }

        // Try to find existing user or return null
        $item = JoomlaMigrationItem::where('migration_id', $this->migration->id)
            ->where('type', 'users')
            ->where('joomla_id', $joomlaAuthorId)
            ->where('status', JoomlaMigrationItem::STATUS_COMPLETED)
            ->first();

        return $item?->local_id;
    }

    /**
     * Create page blocks from content.
     */
    protected function createPageBlocks(Page $page, array $data): void
    {
        $content = $page->content;

        if (!is_array($content) || empty($content)) {
            return;
        }

        // Split content into blocks based on headings or paragraphs
        $text = is_string($content) ? $content : ($content['content'] ?? '');
        $parts = preg_split('/(<h[1-6][^>]*>.*?<\/h[1-6]>)/i', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        $order = 0;

        for ($i = 0; $i < count($parts); $i++) {
            if (empty(trim($parts[$i]))) {
                continue;
            }

            if (preg_match('/<h([1-6])[^>]*>(.*?)<\/h[1-6]>/i', $parts[$i], $matches)) {
                PageBlock::create([
                    'page_id' => $page->id,
                    'type' => 'heading',
                    'content' => ['text' => strip_tags($matches[2]), 'level' => (int) $matches[1]],
                    'order' => $order++,
                ]);

                // Next part is the content after heading
                if (isset($parts[$i + 1]) && !empty(trim($parts[$i + 1]))) {
                    PageBlock::create([
                        'page_id' => $page->id,
                        'type' => 'content',
                        'content' => ['html' => trim($parts[$i + 1])],
                        'order' => $order++,
                    ]);
                    $i++;
                }
            } else {
                PageBlock::create([
                    'page_id' => $page->id,
                    'type' => 'content',
                    'content' => ['html' => trim($parts[$i])],
                    'order' => $order++,
                ]);
            }
        }
    }
}
