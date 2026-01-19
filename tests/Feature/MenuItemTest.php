<?php

use App\Enums\UrlType;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;

uses()->group('menu');

beforeEach(function () {
    Menu::query()->delete();
    MenuItem::query()->delete();
});

it('can create a menu item with custom URL', function () {
    $menu = Menu::factory()->create();

    $menuItem = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'title' => 'Home',
        'url_type' => UrlType::Custom,
        'custom_url' => '/',
        'order' => 1,
    ]);

    expect($menuItem->getUrl())->toBe('/');
});

it('can create a menu item with route', function () {
    $menu = Menu::factory()->create();

    $menuItem = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'title' => 'About',
        'url_type' => UrlType::Route,
        'route_name' => 'about',
    ]);

    expect($menuItem->url_type)->toBe(UrlType::Route);
    expect($menuItem->route_name)->toBe('about');
});

it('can create a menu item with page', function () {
    $menu = Menu::factory()->create();
    $page = Page::factory()->create([
        'slug' => 'test-page',
    ]);

    $menuItem = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'title' => 'Test Page',
        'url_type' => UrlType::Page,
        'page_id' => $page->id,
    ]);

    expect($menuItem->url_type)->toBe(UrlType::Page);
    expect($menuItem->page_id)->toBe($page->id);
});

it('can create a menu item with external URL', function () {
    $menu = Menu::factory()->create();

    $menuItem = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'title' => 'External Link',
        'url_type' => UrlType::External,
        'custom_url' => 'https://example.com',
        'target_blank' => true,
    ]);

    expect($menuItem->getUrl())->toBe('https://example.com');
    expect($menuItem->target_blank)->toBeTrue();
});

it('can create hierarchical menu items', function () {
    $menu = Menu::factory()->create();

    $parent = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'title' => 'Parent',
        'url_type' => UrlType::Custom,
        'custom_url' => '/parent',
    ]);

    $child = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'parent_id' => $parent->id,
        'title' => 'Child',
        'url_type' => UrlType::Custom,
        'custom_url' => '/child',
    ]);

    expect($parent->children)->toHaveCount(1);
    expect($parent->children->first()->title)->toBe('Child');
    expect($child->parent)->not->toBeNull();
    expect($child->parent->title)->toBe('Parent');
});

it('can check if menu item has children', function () {
    $menu = Menu::factory()->create();

    $parent = MenuItem::factory()->create([
        'menu_id' => $menu->id,
    ]);

    $child = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'parent_id' => $parent->id,
    ]);

    expect($parent->hasChildren())->toBeTrue();
    expect($child->hasChildren())->toBeFalse();
});

it('can get menu item with children recursively', function () {
    $menu = Menu::factory()->create();

    $parent = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'title' => 'Parent',
        'url_type' => UrlType::Custom,
        'custom_url' => '/parent',
        'is_active' => true,
    ]);

    $child = MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'parent_id' => $parent->id,
        'title' => 'Child',
        'url_type' => UrlType::Custom,
        'custom_url' => '/child',
        'is_active' => true,
    ]);

    $tree = $parent->withChildren();

    expect($tree['title'])->toBe('Parent');
    expect($tree['children'])->toHaveCount(1);
    expect($tree['children'][0]['title'])->toBe('Child');
});

it('scopes active menu items correctly', function () {
    $menu = Menu::factory()->create();

    MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'is_active' => true,
    ]);

    MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'is_active' => false,
    ]);

    $activeItems = MenuItem::byMenu($menu->id)->active()->get();

    expect($activeItems)->toHaveCount(1);
});

it('orders menu items by order column', function () {
    $menu = Menu::factory()->create();

    MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'title' => 'First',
        'order' => 2,
    ]);

    MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'title' => 'Second',
        'order' => 1,
    ]);

    $items = $menu->items()->get();

    expect($items->first()->title)->toBe('Second');
    expect($items->last()->title)->toBe('First');
});
