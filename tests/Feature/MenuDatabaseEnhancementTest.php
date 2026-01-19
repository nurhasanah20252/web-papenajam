<?php

use App\Enums\MenuLocation;
use App\Enums\UrlType;
use App\Models\Menu;
use App\Models\MenuItem;

test('it can create a menu with multiple locations', function () {
    $menu = Menu::create([
        'name' => 'Main Menu',
        'location' => MenuLocation::Header,
        'locations' => ['header', 'mobile'],
        'max_depth' => 3,
    ]);

    expect($menu->locations)->toBeArray()
        ->and($menu->locations)->toContain('header', 'mobile');
});

test('it can create a menu item with enhanced fields', function () {
    $menu = Menu::create([
        'name' => 'Main Menu',
        'location' => MenuLocation::Header,
        'max_depth' => 3,
    ]);

    $menuItem = MenuItem::create([
        'menu_id' => $menu->id,
        'title' => 'Dashboard',
        'url_type' => UrlType::Route,
        'type' => 'route',
        'route_name' => 'dashboard',
        'target' => '_self',
        'class_name' => 'nav-item-special',
        'display_rules' => ['roles' => ['admin', 'editor']],
    ]);

    expect($menuItem->type)->toBe('route')
        ->and($menuItem->target)->toBe('_self')
        ->and($menuItem->class_name)->toBe('nav-item-special')
        ->and($menuItem->display_rules)->toBeArray()
        ->and($menuItem->display_rules['roles'])->toContain('admin', 'editor');
});
