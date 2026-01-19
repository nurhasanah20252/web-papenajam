<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Cache TTL in seconds (24 hours).
     */
    private const CACHE_TTL = 86400;

    /**
     * Cache TTL for settings (1 hour).
     */
    private const SETTINGS_CACHE_TTL = 3600;

    /**
     * Get menu by location with caching.
     */
    public function getMenu(string $location): ?array
    {
        return Cache::remember(
            "menu:{$location}",
            self::CACHE_TTL,
            fn () => $this->loadMenu($location)
        );
    }

    /**
     * Clear menu cache for a specific location.
     */
    public function clearMenuCache(string $location): void
    {
        Cache::forget("menu:{$location}");
    }

    /**
     * Clear all menu caches.
     */
    public function clearAllMenuCache(): void
    {
        Cache::forget('menu:header');
        Cache::forget('menu:footer');
        Cache::forget('menu:sidebar');
    }

    /**
     * Get setting value with caching.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return Cache::remember(
            "setting:{$key}",
            self::SETTINGS_CACHE_TTL,
            fn () => Setting::where('key', $key)->first()?->value ?? $default
        );
    }

    /**
     * Get settings group with caching.
     */
    public function getSettingsGroup(string $group): array
    {
        return Cache::remember(
            "settings:group:{$group}",
            self::SETTINGS_CACHE_TTL,
            fn () => Setting::where('group', $group)
                ->get()
                ->pluck('value', 'key')
                ->toArray()
        );
    }

    /**
     * Get all public settings with caching.
     */
    public function getPublicSettings(): array
    {
        return Cache::remember(
            'settings:public',
            self::SETTINGS_CACHE_TTL,
            fn () => Setting::where('is_public', true)
                ->get()
                ->pluck('value', 'key')
                ->toArray()
        );
    }

    /**
     * Clear settings cache.
     */
    public function clearSettingsCache(): void
    {
        Cache::forget('settings:public');
    }

    /**
     * Clear setting cache by key.
     */
    public function clearSettingCache(string $key): void
    {
        Cache::forget("setting:{$key}");
    }

    /**
     * Get categories by type with caching.
     */
    public function getCategories(string $type): array
    {
        return Cache::remember(
            "categories:{$type}",
            self::CACHE_TTL,
            fn () => Category::where('type', $type)
                ->orderBy('order')
                ->get()
                ->toArray()
        );
    }

    /**
     * Clear categories cache by type.
     */
    public function clearCategoriesCache(string $type): void
    {
        Cache::forget("categories:{$type}");
    }

    /**
     * Get featured news with caching.
     */
    public function getFeaturedNews(int $limit = 5): array
    {
        return Cache::remember(
            "news:featured:{$limit}",
            self::CACHE_TTL,
            fn () => \App\Models\News::query()
                ->with(['category', 'author'])
                ->published()
                ->featured()
                ->latest('published_at')
                ->limit($limit)
                ->get()
                ->toArray()
        );
    }

    /**
     * Clear featured news cache.
     */
    public function clearFeaturedNewsCache(): void
    {
        Cache::forget('news:featured:5');
        Cache::forget('news:featured:10');
    }

    /**
     * Get latest news with caching.
     */
    public function getLatestNews(int $limit = 5): array
    {
        return Cache::remember(
            "news:latest:{$limit}",
            self::CACHE_TTL,
            fn () => \App\Models\News::query()
                ->with(['category'])
                ->published()
                ->latest('published_at')
                ->limit($limit)
                ->get()
                ->toArray()
        );
    }

    /**
     * Clear latest news cache.
     */
    public function clearLatestNewsCache(): void
    {
        Cache::forget('news:latest:5');
        Cache::forget('news:latest:10');
    }

    /**
     * Clear all application caches.
     */
    public function clearAllCaches(): void
    {
        $this->clearAllMenuCache();
        $this->clearSettingsCache();
        $this->clearFeaturedNewsCache();
        $this->clearLatestNewsCache();
        Cache::forget('categories:news');
        Cache::forget('categories:document');
        Cache::forget('categories:page');
    }

    /**
     * Load menu tree from database.
     */
    private function loadMenu(string $location): ?array
    {
        $menu = Menu::where('location', $location)->first();

        if (! $menu) {
            return null;
        }

        return $menu->getTree();
    }

    /**
     * Remember a query result with tags for cache invalidation.
     */
    public function rememberWithTags(string $key, int $ttl, callable $callback, array $tags = []): mixed
    {
        if (Cache::supportsTags() && ! empty($tags)) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Flush cache by tags.
     */
    public function flushTag(string $tag): void
    {
        if (Cache::supportsTags()) {
            Cache::tags($tag)->flush();
        }
    }
}
