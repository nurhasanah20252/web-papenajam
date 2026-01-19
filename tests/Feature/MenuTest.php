<?php

use App\Enums\MenuLocation;
use App\Models\Menu;

uses()->group('menu');

beforeEach(function () {
    Menu::query()->delete();
});

it('can create a menu', function () {
    $menu = Menu::factory()->create([
        'name' => 'Main Menu',
        'location' => MenuLocation::Header,
        'max_depth' => 3,
    ]);

    expect($menu->name)->toBe('Main Menu');
    expect($menu->location)->toBe(MenuLocation::Header);
    expect($menu->max_depth)->toBe(3);
});

it('can get menu tree', function () {
    $menu = Menu::factory()->create([
        'name' => 'Main Menu',
        'location' => MenuLocation::Header,
    ]);

    $parentItem = \App\Models\MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'title' => 'Parent Item',
        'url_type' => \App\Enums\UrlType::Custom,
        'custom_url' => '/parent',
        'order' => 1,
    ]);

    $childItem = \App\Models\MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'parent_id' => $parentItem->id,
        'title' => 'Child Item',
        'url_type' => \App\Enums\UrlType::Custom,
        'custom_url' => '/child',
        'order' => 1,
    ]);

    $tree = $menu->getTree();

    expect($tree)->toHaveCount(1);
    expect($tree[0]['title'])->toBe('Parent Item');
    expect($tree[0]['children'])->toHaveCount(1);
    expect($tree[0]['children'][0]['title'])->toBe('Child Item');
});

it('can get menu by location scope', function () {
    Menu::factory()->create([
        'name' => 'Header Menu',
        'location' => MenuLocation::Header,
    ]);

    Menu::factory()->create([
        'name' => 'Footer Menu',
        'location' => MenuLocation::Footer,
    ]);

    $headerMenu = Menu::byLocation(MenuLocation::Header)->first();
    $footerMenu = Menu::byLocation(MenuLocation::Footer)->first();

    expect($headerMenu->name)->toBe('Header Menu');
    expect($footerMenu->name)->toBe('Footer Menu');
});

it('can check if menu has items', function () {
    $menuWithItems = Menu::factory()->create([
        'location' => MenuLocation::Header,
    ]);
    \App\Models\MenuItem::factory()->create([
        'menu_id' => $menuWithItems->id,
    ]);

    $menuWithoutItems = Menu::factory()->create([
        'location' => MenuLocation::Footer,
    ]);

    expect($menuWithItems->hasItems())->toBeTrue();
    expect($menuWithoutItems->hasItems())->toBeFalse();
});

it('respects max_depth configuration', function () {
    $menu = Menu::factory()->create([
        'max_depth' => 2,
    ]);

    expect($menu->max_depth)->toBe(2);
});
