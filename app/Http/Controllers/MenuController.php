<?php

namespace App\Http\Controllers;

use App\Enums\MenuLocation;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\CacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function __construct(
        private CacheService $cacheService
    ) {}

    /**
     * List all menus.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Menu::withCount('items')->get(),
        ]);
    }

    /**
     * Display the specified menu with hierarchical items.
     */
    public function show(Menu $menu): JsonResponse
    {
        return response()->json([
            'data' => array_merge($menu->toArray(), [
                'tree' => $menu->getTree(false), // Include inactive for management
            ]),
        ]);
    }

    /**
     * Show the menu builder editor.
     */
    public function edit(Menu $menu): \Inertia\Response
    {
        return \Inertia\Inertia::render('menu/Editor', [
            'menu' => $menu,
            'items' => $menu->getTree(false),
            'pages' => \App\Models\Page::select('id', 'title', 'slug')->get(),
            'locations' => array_map(fn ($loc) => [
                'value' => $loc->value,
                'label' => $loc->label(),
            ], MenuLocation::cases()),
        ]);
    }

    /**
     * Get menu by location as hierarchical tree.
     */
    public function getByLocation(MenuLocation $location): JsonResponse
    {
        $menuTree = $this->cacheService->getMenu($location->value);

        if (! $menuTree) {
            return response()->json(['data' => []]);
        }

        return response()->json([
            'data' => $menuTree,
        ]);
    }

    /**
     * Get menu items for a specific menu as flat list with parent info.
     */
    public function items(Menu $menu): JsonResponse
    {
        $items = $menu->items()
            ->with(['parent', 'page'])
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'url' => $item->getUrl(),
                'url_type' => $item->url_type?->value,
                'icon' => $item->icon,
                'order' => $item->order,
                'target_blank' => $item->target_blank,
                'is_active' => $item->is_active,
                'parent_id' => $item->parent_id,
                'depth' => $this->calculateDepth($item),
            ]);

        return response()->json(['data' => $items]);
    }

    /**
     * Update menu structure (reordering and hierarchy).
     */
    public function updateStructure(Request $request, Menu $menu): JsonResponse
    {
        $items = $request->input('items', []);

        if (empty($items)) {
            return response()->json(['message' => 'No items provided'], 422);
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($items, $menu) {
            $this->recursiveUpdate($items, null, $menu->id);
        });

        // Clear menu cache
        $this->cacheService->clearMenuCache($menu->location->value);
        if ($menu->locations) {
            foreach ($menu->locations as $loc) {
                $this->cacheService->clearMenuCache($loc);
            }
        }

        return response()->json([
            'message' => 'Menu structure updated successfully',
        ]);
    }

    /**
     * Recursively update menu items order and parent.
     */
    protected function recursiveUpdate(array $items, ?int $parentId, int $menuId): void
    {
        foreach ($items as $index => $item) {
            MenuItem::where('id', $item['id'])
                ->where('menu_id', $menuId)
                ->update([
                    'order' => $index,
                    'parent_id' => $parentId,
                ]);

            if (isset($item['children']) && is_array($item['children'])) {
                $this->recursiveUpdate($item['children'], $item['id'], $menuId);
            }
        }
    }

    /**
     * Update menu locations.
     */
    public function storeLocation(\App\Http\Requests\Menu\UpdateMenuLocationRequest $request, Menu $menu): JsonResponse
    {
        $menu->update($request->validated());

        // Clear caches
        $this->cacheService->clearAllMenuCache();

        return response()->json([
            'message' => 'Menu locations updated successfully',
            'data' => $menu,
        ]);
    }

    /**
     * Reorder menu items.
     *
     * @deprecated Use updateStructure instead
     */
    public function reorder(Request $request, Menu $menu): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:menu_items,id',
            'items.*.order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            $menuItem = $menu->items()->find($item['id']);
            if ($menuItem) {
                $menuItem->update(['order' => $item['order']]);
            }
        }

        // Clear menu cache after reordering
        $this->cacheService->clearMenuCache($menu->location);

        return response()->json([
            'message' => 'Menu items reordered successfully',
        ]);
    }

    /**
     * Calculate the depth of a menu item in the tree.
     */
    protected function calculateDepth($item, int $depth = 0): int
    {
        if ($item->parent_id === null) {
            return $depth;
        }

        return $this->calculateDepth($item->parent, $depth + 1);
    }
}
