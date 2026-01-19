<?php

namespace App\Services\JoomlaMigration;

use App\Models\Menu;
use App\Models\MenuItem;

class MenuMigrationService extends BaseMigrationService
{
    protected array $menuIdMap = [];

    public function getType(): string
    {
        return JoomlaMigration::TYPE_MENUS;
    }

    public function getModelClass(): string
    {
        return Menu::class;
    }

    public function validateData(array $data): bool
    {
        return ! empty($data['title'] ?? $data['menutype'] ?? null);
    }

    public function transformData(array $data): array
    {
        return [
            'name' => $data['title'] ?? $data['menutype'] ?? '',
            'location' => $this->mapLocation($data['menutype'] ?? $data['title'] ?? ''),
            'is_active' => true,
        ];
    }

    public function saveData(array $data): int
    {
        $menu = Menu::create($data);
        $this->menuIdMap[$this->migration->id] ??= [];
        $this->menuIdMap[$this->migration->id]['menus'][$menu->id] = true;

        return $menu->id;
    }

    /**
     * Map Joomla menu type to location.
     */
    protected function mapLocation(string $menutype): string
    {
        $locationMap = [
            'mainmenu' => 'main',
            'main' => 'main',
            'topmenu' => 'top',
            'footer' => 'footer',
            'bottom' => 'bottom',
            'user' => 'user',
            'default' => 'main',
        ];

        return $locationMap[strtolower($menutype)] ?? strtolower($menutype);
    }

    /**
     * Process menu items for a menu.
     */
    public function processMenuItems(int $menuId, array $items): void
    {
        $this->processItemsRecursive($items, null, $menuId, 0);
    }

    /**
     * Recursively process menu items.
     */
    protected function processItemsRecursive(array $items, ?int $parentId, int $menuId, int $level): void
    {
        $order = 0;

        foreach ($items as $item) {
            $this->processMenuItem($item, $parentId, $menuId, $order++, $level);

            // Process children if any
            if (! empty($item['children'] ?? [])) {
                $this->processItemsRecursive($item['children'], $item['local_id'] ?? null, $menuId, 0, $level + 1);
            }
        }
    }

    /**
     * Process a single menu item.
     */
    protected function processMenuItem(array $item, ?int $parentId, int $menuId, int $order, int $level): void
    {
        $migrationItem = JoomlaMigrationItem::create([
            'migration_id' => $this->migration->id,
            'type' => 'menu_items',
            'joomla_id' => $item['id'] ?? 0,
            'joomla_data' => $item,
            'status' => JoomlaMigrationItem::STATUS_PROCESSING,
        ]);

        try {
            $data = $this->transformMenuItemData($item, $parentId, $menuId, $order);
            $menuItem = MenuItem::create($data);

            $migrationItem->update([
                'local_id' => $menuItem->id,
                'local_model' => MenuItem::class,
                'status' => JoomlaMigrationItem::STATUS_COMPLETED,
            ]);

            // Store local ID in item for child processing
            $item['local_id'] = $menuItem->id;
        } catch (\Throwable $e) {
            $migrationItem->markAsFailed($e->getMessage());
        }
    }

    /**
     * Transform menu item data.
     */
    protected function transformMenuItemData(array $item, ?int $parentId, int $menuId, int $order): array
    {
        $title = $item['title'] ?? $item['name'] ?? '';
        $type = $this->determineItemType($item);
        $url = $this->buildUrl($item, $type);
        $pageId = $this->mapPage($item);

        return [
            'menu_id' => $menuId,
            'parent_id' => $parentId,
            'title' => $title,
            'type' => $pageId ? 'page' : $type,
            'url' => $url,
            'page_id' => $pageId,
            'order' => $order,
            'icon' => $this->mapIcon($item),
            'css_class' => $item['params']['menu_item_css_class'] ?? $item['params']['class_sfx'] ?? null,
            'target_blank' => ($item['params']['target'] ?? '') === '_blank',
            'is_active' => ($item['published'] ?? $item['state'] ?? 1) == 1,
        ];
    }

    /**
     * Determine menu item type.
     */
    protected function determineItemType(array $item): string
    {
        $link = $item['link'] ?? '';

        if (empty($link) || $link === '#') {
            return 'custom';
        }

        if (str_contains($link, 'http://') || str_contains($link, 'https://')) {
            return 'external';
        }

        if (str_contains($link, 'index.php?option=com_content')) {
            return 'page';
        }

        return 'custom';
    }

    /**
     * Build URL from item.
     */
    protected function buildUrl(array $item, string $type): string
    {
        $link = $item['link'] ?? '';

        if (empty($link)) {
            return '#';
        }

        // Use JoomlaDataCleaner to convert links
        return app(JoomlaDataCleaner::class)->convertLinks($link);
    }

    /**
     * Map menu item icon.
     */
    protected function mapIcon(array $item): ?string
    {
        // Joomla uses different icon systems, try to map common ones
        $iconClass = $item['params']['menu_image'] ?? $item['params']['menu_icon'] ?? null;

        if (empty($iconClass)) {
            return null;
        }

        // If it's an image path, return null for icon
        if (str_contains($iconClass, '/')) {
            return null;
        }

        return $iconClass;
    }

    /**
     * Map menu item to local page or news.
     */
    protected function mapPage(array $item): ?int
    {
        // Extract article ID from link
        if (! preg_match('/id=(\d+)/', $item['link'] ?? '', $matches)) {
            return null;
        }

        $articleId = (int) $matches[1];

        // Try to find in pages first
        $migrationItem = \App\Models\JoomlaMigrationItem::where('migration_id', $this->migration->id)
            ->where('type', \App\Models\JoomlaMigration::TYPE_PAGES)
            ->where('joomla_id', $articleId)
            ->where('status', \App\Models\JoomlaMigrationItem::STATUS_COMPLETED)
            ->first();

        if ($migrationItem) {
            return $migrationItem->local_id;
        }

        // Then try to find in news
        $migrationItem = \App\Models\JoomlaMigrationItem::where('migration_id', $this->migration->id)
            ->where('type', \App\Models\JoomlaMigration::TYPE_NEWS)
            ->where('joomla_id', $articleId)
            ->where('status', \App\Models\JoomlaMigrationItem::STATUS_COMPLETED)
            ->first();

        return $migrationItem?->local_id;
    }
}
