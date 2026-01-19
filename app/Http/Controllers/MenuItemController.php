<?php

namespace App\Http\Controllers;

use App\Http\Requests\Menu\StoreMenuItemRequest;
use App\Http\Requests\Menu\UpdateMenuItemRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;

class MenuItemController extends Controller
{
    /**
     * Store a newly created menu item in storage.
     */
    public function store(StoreMenuItemRequest $request, Menu $menu): JsonResponse
    {
        $item = $menu->items()->create($request->validated());

        return response()->json([
            'message' => 'Menu item created successfully',
            'data' => $item,
        ], 201);
    }

    /**
     * Display the specified menu item.
     */
    public function show(Menu $menu, MenuItem $item): JsonResponse
    {
        return response()->json([
            'data' => $item,
        ]);
    }

    /**
     * Update the specified menu item in storage.
     */
    public function update(UpdateMenuItemRequest $request, Menu $menu, MenuItem $item): JsonResponse
    {
        $item->update($request->validated());

        return response()->json([
            'message' => 'Menu item updated successfully',
            'data' => $item,
        ]);
    }

    /**
     * Remove the specified menu item from storage.
     */
    public function destroy(Menu $menu, MenuItem $item): JsonResponse
    {
        $item->delete();

        return response()->json([
            'message' => 'Menu item deleted successfully',
        ]);
    }
}
