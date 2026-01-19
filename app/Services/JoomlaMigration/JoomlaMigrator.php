<?php

namespace App\Services\JoomlaMigration;

use App\Enums\CategoryType;
use App\Enums\NewsStatus;
use App\Enums\PageStatus;
use App\Enums\PageType;
use App\Models\Category;
use App\Models\JoomlaMigration;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\News;
use App\Models\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class JoomlaMigrator
{
    protected string $dataPath;

    protected array $stats = [
        'success' => 0,
        'failed' => 0,
        'skipped' => 0,
    ];

    public function __construct()
    {
        $this->dataPath = base_path('docs');
    }

    /**
     * Load JSON data from file.
     */
    protected function loadData(string $filename): array
    {
        $path = $this->dataPath.'/'.$filename;

        if (! file_exists($path)) {
            throw new \Exception("Joomla export file not found: {$filename}");
        }

        $content = file_get_contents($path);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON in file: {$filename}");
        }

        return $data;
    }

    /**
     * Generate a unique slug.
     */
    protected function generateSlug(string $title, string $prefix = ''): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (Page::where('slug', $slug)->exists() || News::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter++;
        }

        return $slug;
    }

    /**
     * Generate data hash for change detection.
     */
    protected function generateDataHash(array $data): string
    {
        return md5(json_encode($data));
    }

    /**
     * Check if record was already migrated.
     */
    protected function wasMigrated(string $sourceTable, int $sourceId): ?JoomlaMigration
    {
        return JoomlaMigration::bySourceTable($sourceTable)
            ->where('source_id', $sourceId)
            ->first();
    }

    /**
     * Record migration attempt.
     */
    protected function recordMigration(
        string $sourceTable,
        int $sourceId,
        ?int $targetId,
        string $status,
        ?string $error = null,
        ?string $dataHash = null
    ): JoomlaMigration {
        return JoomlaMigration::updateOrCreate(
            [
                'source_table' => $sourceTable,
                'source_id' => $sourceId,
            ],
            [
                'target_id' => $targetId,
                'data_hash' => $dataHash,
                'migration_status' => $status,
                'error_message' => $error,
                'migrated_at' => $status === 'success' ? now() : null,
            ]
        );
    }

    /**
     * Clean HTML content.
     */
    protected function cleanHtml(string $html): string
    {
        // Remove Joomla-specific tags
        $html = preg_replace('/\{loadmodule[^\}]*\}/i', '', $html);
        $html = preg_replace('/\{mosimage[^\}]*\}/i', '', $html);

        // Clean up multiple empty lines
        $html = preg_replace('/\n\s*\n\s*\n/', "\n\n", $html);

        // Convert absolute URLs to relative (if they point to the same domain)
        $html = preg_replace('/https?:\/\/[^\/]+(\/[^\s"\'<]+)/i', '$1', $html);

        return trim($html);
    }

    /**
     * Extract excerpt from content.
     */
    protected function extractExcerpt(string $content, int $length = 200): string
    {
        // Strip HTML tags
        $text = strip_tags($content);
        // Trim and limit length
        $text = trim($text);
        if (strlen($text) > $length) {
            $text = substr($text, 0, $length).'...';
        }

        return $text;
    }

    /**
     * Convert HTML to content blocks.
     */
    protected function htmlToBlocks(string $html, string $type = 'text'): array
    {
        return [
            [
                'type' => $type,
                'content' => $this->cleanHtml($html),
            ],
        ];
    }

    /**
     * Parse Joomla link and determine target type.
     */
    protected function parseJoomlaLink(string $link): array
    {
        $result = [
            'type' => 'external',
            'url' => $link,
            'route' => null,
            'params' => [],
        ];

        // Parse URL parameters
        if (strpos($link, 'index.php') !== false) {
            parse_str(parse_url($link, PHP_URL_QUERY), $params);

            $result['params'] = $params;

            if (isset($params['option'])) {
                switch ($params['option']) {
                    case 'com_content':
                        if (isset($params['view']) && $params['view'] === 'article') {
                            $result['type'] = 'article';
                        } elseif (isset($params['view']) && $params['view'] === 'category') {
                            $result['type'] = 'category';
                        }
                        break;
                }
            }
        } elseif ($link === '#') {
            $result['type'] = 'placeholder';
        } elseif (strpos($link, 'http') === 0) {
            $result['type'] = 'external';
        } else {
            $result['type'] = 'internal';
        }

        return $result;
    }

    /**
     * Get migration statistics.
     */
    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * Reset statistics.
     */
    protected function resetStats(): void
    {
        $this->stats = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];
    }

    /**
     * Migrate Joomla categories to Laravel categories.
     */
    public function migrateCategories(bool $force = false): array
    {
        $this->resetStats();
        $data = $this->loadData('joomla_categories.json');

        // Filter out ROOT category and sort by parent_id
        $categories = collect($data)
            ->filter(fn ($cat) => $cat['id'] !== 1)
            ->sortBy('parent_id')
            ->values();

        foreach ($categories as $joomlaCategory) {
            try {
                // Check if already migrated
                $existing = $this->wasMigrated('categories', $joomlaCategory['id']);

                if ($existing && ! $force) {
                    $this->stats['skipped']++;

                    continue;
                }

                // Determine category type
                $categoryType = CategoryType::News;
                if (in_array($joomlaCategory['extension'] ?? '', ['com_banners', 'com_contact', 'com_newsfeeds'])) {
                    // Skip non-content categories
                    $this->stats['skipped']++;

                    continue;
                }

                // Find parent category ID
                $parentId = null;
                if ($joomlaCategory['parent_id'] > 1) {
                    $parentMigration = $this->wasMigrated('categories', $joomlaCategory['parent_id']);
                    if ($parentMigration) {
                        $parentId = $parentMigration->target_id;
                    }
                }

                // Generate slug
                $slug = Str::slug($joomlaCategory['title']);
                $counter = 1;
                while (Category::where('slug', $slug)->exists()) {
                    $slug = Str::slug($joomlaCategory['title']).'-'.$counter++;
                }

                // Create category
                $category = Category::create([
                    'name' => $joomlaCategory['title'],
                    'slug' => $slug,
                    'description' => null,
                    'parent_id' => $parentId,
                    'type' => $categoryType,
                    'icon' => null,
                    'order' => 0,
                ]);

                // Record migration
                $this->recordMigration(
                    'categories',
                    $joomlaCategory['id'],
                    $category->id,
                    'success',
                    null,
                    $this->generateDataHash($joomlaCategory)
                );

                $this->stats['success']++;
            } catch (\Exception $e) {
                $this->recordMigration(
                    'categories',
                    $joomlaCategory['id'],
                    null,
                    'failed',
                    $e->getMessage()
                );

                $this->stats['failed']++;
                Log::error("Failed to migrate category {$joomlaCategory['id']}: {$e->getMessage()}");
            }
        }

        return $this->stats;
    }

    /**
     * Migrate Joomla content to Pages and News.
     */
    public function migrateContent(bool $force = false): array
    {
        $this->resetStats();
        $data = $this->loadData('joomla_content.json');

        foreach ($data as $joomlaArticle) {
            try {
                // Check if already migrated
                $existing = $this->wasMigrated('content', $joomlaArticle['id']);

                if ($existing && ! $force) {
                    $this->stats['skipped']++;

                    continue;
                }

                // Determine if this should be a Page or News
                $categoryMigration = null;
                if (! empty($joomlaArticle['category_id'])) {
                    $categoryMigration = $this->wasMigrated('categories', $joomlaArticle['category_id']);
                }

                // If category exists and is news type, create News, otherwise create Page
                $isNews = $categoryMigration !== null;

                $slug = $this->generateSlug($joomlaArticle['alias'] ?? $joomlaArticle['title']);
                $excerpt = $this->extractExcerpt($joomlaArticle['content']);
                $contentBlocks = $this->htmlToBlocks($joomlaArticle['content']);

                if ($isNews) {
                    // Create News article
                    $news = News::create([
                        'title' => $joomlaArticle['title'],
                        'slug' => $slug,
                        'excerpt' => $excerpt,
                        'content' => $contentBlocks,
                        'featured_image' => null,
                        'is_featured' => false,
                        'views_count' => 0,
                        'category_id' => $categoryMigration?->target_id,
                        'author_id' => 1, // Default to admin
                        'status' => ($joomlaArticle['status'] ?? 0) == 1 ? NewsStatus::Published : NewsStatus::Draft,
                        'published_at' => $joomlaArticle['created_at'] ?? now(),
                        'tags' => [],
                    ]);

                    $targetId = $news->id;
                    $targetType = 'news';
                } else {
                    // Create Page
                    $page = Page::create([
                        'slug' => $slug,
                        'title' => $joomlaArticle['title'],
                        'excerpt' => $excerpt,
                        'content' => $contentBlocks,
                        'meta' => [
                            'description' => $excerpt,
                            'keywords' => [],
                        ],
                        'featured_image' => null,
                        'status' => ($joomlaArticle['status'] ?? 0) == 1 ? PageStatus::Published : PageStatus::Draft,
                        'page_type' => PageType::Standard,
                        'author_id' => 1, // Default to admin
                        'template_id' => null,
                        'published_at' => $joomlaArticle['created_at'] ?? now(),
                        'view_count' => 0,
                    ]);

                    $targetId = $page->id;
                    $targetType = 'pages';
                }

                // Record migration
                $this->recordMigration(
                    $targetType,
                    $joomlaArticle['id'],
                    $targetId,
                    'success',
                    null,
                    $this->generateDataHash($joomlaArticle)
                );

                $this->stats['success']++;
            } catch (\Exception $e) {
                // Determine target type
                $categoryMigration = ! empty($joomlaArticle['category_id'])
                    ? $this->wasMigrated('categories', $joomlaArticle['category_id'])
                    : null;
                $targetType = $categoryMigration !== null ? 'news' : 'pages';

                $this->recordMigration(
                    $targetType,
                    $joomlaArticle['id'],
                    null,
                    'failed',
                    $e->getMessage()
                );

                $this->stats['failed']++;
                Log::error("Failed to migrate content {$joomlaArticle['id']}: {$e->getMessage()}");
            }
        }

        return $this->stats;
    }

    /**
     * Migrate Joomla menus and menu items.
     */
    public function migrateMenus(bool $force = false): array
    {
        $this->resetStats();
        $data = $this->loadData('joomla_menu.json');

        // Group by menutype
        $menusByType = collect($data)->groupBy('menutype');

        foreach ($menusByType as $menuType => $items) {
            try {
                // Check if menu was already created
                $menuMigration = $this->wasMigrated('menus', md5($menuType));

                if (! $menuMigration || $force) {
                    // Create menu
                    $menu = Menu::create([
                        'name' => $menuType,
                        'location' => 'header', // Default location
                        'max_depth' => 3,
                        'description' => "Migrated from Joomla: {$menuType}",
                    ]);

                    $menuMigration = $this->recordMigration(
                        'menus',
                        md5($menuType),
                        $menu->id,
                        'success'
                    );
                }

                $menuId = $menuMigration->target_id;

                // Sort menu items by level
                $sortedItems = $items->sortBy('level')->values();

                foreach ($sortedItems as $joomlaItem) {
                    try {
                        // Skip root item
                        if ($joomlaItem['id'] == 1) {
                            continue;
                        }

                        // Check if already migrated
                        $existing = $this->wasMigrated('menu_items', $joomlaItem['id']);

                        if ($existing && ! $force) {
                            $this->stats['skipped']++;

                            continue;
                        }

                        // Find parent menu item
                        $parentId = null;
                        if ($joomlaItem['parent_id'] > 1) {
                            $parentMigration = $this->wasMigrated('menu_items', $joomlaItem['parent_id']);
                            if ($parentMigration) {
                                $parentId = $parentMigration->target_id;
                            }
                        }

                        // Parse link
                        $linkInfo = $this->parseJoomlaLink($joomlaItem['link'] ?? '#');

                        // Determine URL
                        $url = $joomlaItem['link'] ?? '#';
                        if ($linkInfo['type'] === 'article') {
                            // Try to find migrated article
                            $articleId = $linkInfo['params']['id'] ?? null;
                            if ($articleId) {
                                $pageMigration = $this->wasMigrated('pages', $articleId);
                                $newsMigration = $this->wasMigrated('news', $articleId);

                                if ($pageMigration) {
                                    $page = Page::find($pageMigration->target_id);
                                    $url = $page ? $page->getUrl() : '#';
                                } elseif ($newsMigration) {
                                    $news = News::find($newsMigration->target_id);
                                    $url = $news ? $news->getUrl() : '#';
                                }
                            }
                        } elseif ($linkInfo['type'] === 'category') {
                            // Try to find migrated category
                            $categoryId = $linkInfo['params']['id'] ?? null;
                            if ($categoryId) {
                                $categoryMigration = $this->wasMigrated('categories', $categoryId);
                                if ($categoryMigration) {
                                    $category = Category::find($categoryMigration->target_id);
                                    $url = $category ? '/category/'.$category->slug : '#';
                                }
                            }
                        }

                        // Create menu item
                        $menuItem = MenuItem::create([
                            'menu_id' => $menuId,
                            'label' => $joomlaItem['title'],
                            'url' => $url,
                            'type' => $linkInfo['type'],
                            'parent_id' => $parentId,
                            'depth' => $joomlaItem['level'],
                            'is_enabled' => ($joomlaItem['published'] ?? 0) == 1,
                            'order' => 0,
                            'target' => '_self',
                            'icon' => null,
                        ]);

                        // Record migration
                        $this->recordMigration(
                            'menu_items',
                            $joomlaItem['id'],
                            $menuItem->id,
                            'success',
                            null,
                            $this->generateDataHash($joomlaItem)
                        );

                        $this->stats['success']++;
                    } catch (\Exception $e) {
                        $this->recordMigration(
                            'menu_items',
                            $joomlaItem['id'],
                            null,
                            'failed',
                            $e->getMessage()
                        );

                        $this->stats['failed']++;
                        Log::error("Failed to migrate menu item {$joomlaItem['id']}: {$e->getMessage()}");
                    }
                }
            } catch (\Exception $e) {
                Log::error("Failed to migrate menu {$menuType}: {$e->getMessage()}");
            }
        }

        return $this->stats;
    }

    /**
     * Rollback migrations for a specific table.
     */
    public function rollback(string $table, bool $deleteRecords = true): array
    {
        $stats = [
            'deleted' => 0,
            'kept' => 0,
        ];

        $migrations = JoomlaMigration::bySourceTable($table)->successful()->get();

        foreach ($migrations as $migration) {
            try {
                if ($deleteRecords && $migration->target_id) {
                    switch ($table) {
                        case 'categories':
                            Category::where('id', $migration->target_id)->delete();
                            break;
                        case 'pages':
                            Page::where('id', $migration->target_id)->delete();
                            break;
                        case 'news':
                            News::where('id', $migration->target_id)->delete();
                            break;
                        case 'menu_items':
                            MenuItem::where('id', $migration->target_id)->delete();
                            break;
                        case 'menus':
                            Menu::where('id', $migration->target_id)->delete();
                            break;
                    }

                    $stats['deleted']++;
                }

                // Delete migration record
                $migration->delete();
            } catch (\Exception $e) {
                $stats['kept']++;
                Log::error("Failed to rollback {$table} record {$migration->source_id}: {$e->getMessage()}");
            }
        }

        return $stats;
    }
}
