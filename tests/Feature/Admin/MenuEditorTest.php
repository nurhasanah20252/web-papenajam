<?php

namespace Tests\Feature\Admin;

use App\Enums\MenuLocation;
use App\Enums\UrlType;
use App\Enums\UserRole;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => UserRole::Admin,
    ]);

    // Force JSON headers to ensure we get JSON responses
    $this->withHeaders(['Accept' => 'application/json']);
});

test('admin can list menus', function () {
    // Explicitly delete to avoid unique constraint issues if any persist from migrations/seeders
    Menu::query()->delete();

    Menu::factory()->create(['location' => MenuLocation::Header]);
    Menu::factory()->create(['location' => MenuLocation::Footer]);
    Menu::factory()->create(['location' => MenuLocation::Sidebar]);

    $response = $this->actingAs($this->admin)
        ->getJson(route('admin.menus.index'));

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

test('admin can view a menu with tree', function () {
    $menu = Menu::factory()->create(['location' => MenuLocation::Mobile]);
    $parent = MenuItem::factory()->create(['menu_id' => $menu->id]);
    MenuItem::factory()->create([
        'menu_id' => $menu->id,
        'parent_id' => $parent->id,
    ]);

    $response = $this->actingAs($this->admin)
        ->getJson(route('admin.menus.show', $menu));

    $response->assertOk()
        ->assertJsonPath('data.name', $menu->name)
        ->assertJsonCount(1, 'data.tree');
});

test('admin can create a menu item', function () {
    $menu = Menu::factory()->create(['location' => MenuLocation::Header]);

    $response = $this->actingAs($this->admin)
        ->postJson(route('admin.menus.items.store', $menu), [
            'title' => 'New Item',
            'url_type' => UrlType::Custom->value,
            'custom_url' => '/new-url',
            'order' => 0,
            'is_active' => true,
        ]);

    $response->assertCreated();
    $this->assertDatabaseHas('menu_items', [
        'menu_id' => $menu->id,
        'title' => 'New Item',
    ]);
});

test('admin can update a menu item', function () {
    $menu = Menu::factory()->create(['location' => MenuLocation::Header]);
    $item = MenuItem::factory()->create(['menu_id' => $menu->id]);

    $response = $this->actingAs($this->admin)
        ->putJson(route('admin.menus.items.update', [$menu, $item]), [
            'title' => 'Updated Title',
            'url_type' => UrlType::Custom->value,
            'custom_url' => '/updated-url',
        ]);

    $response->assertOk();
    $this->assertDatabaseHas('menu_items', [
        'id' => $item->id,
        'title' => 'Updated Title',
    ]);
});

test('admin can delete a menu item', function () {
    $menu = Menu::factory()->create(['location' => MenuLocation::Header]);
    $item = MenuItem::factory()->create(['menu_id' => $menu->id]);

    $response = $this->actingAs($this->admin)
        ->deleteJson(route('admin.menus.items.destroy', [$menu, $item]));

    $response->assertOk();
    $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
});

test('admin can update menu structure', function () {
    $menu = Menu::factory()->create(['location' => MenuLocation::Header]);
    $item1 = MenuItem::factory()->create(['menu_id' => $menu->id, 'order' => 1]);
    $item2 = MenuItem::factory()->create(['menu_id' => $menu->id, 'order' => 2]);

    $response = $this->actingAs($this->admin)
        ->putJson(route('admin.menus.update-structure', $menu), [
            'items' => [
                ['id' => $item1->id, 'children' => [
                    ['id' => $item2->id],
                ]],
            ],
        ]);

    $response->assertOk();
    $this->assertDatabaseHas('menu_items', ['id' => $item1->id, 'order' => 0, 'parent_id' => null]);
    $this->assertDatabaseHas('menu_items', ['id' => $item2->id, 'order' => 0, 'parent_id' => $item1->id]);
});

test('admin can update menu locations', function () {
    $menu = Menu::factory()->create(['location' => MenuLocation::Sidebar]);

    $response = $this->actingAs($this->admin)
        ->putJson(route('admin.menus.store-locations', $menu), [
            'location' => MenuLocation::Footer->value,
            'locations' => ['footer', 'mobile'],
        ]);

    $response->assertOk();
    $this->assertDatabaseHas('menus', [
        'id' => $menu->id,
        'location' => MenuLocation::Footer->value,
    ]);
});

test('unauthorized users cannot manage menus', function () {
    $user = User::factory()->create(['role' => UserRole::Subscriber]);
    $menu = Menu::factory()->create(['location' => MenuLocation::Header]);

    // Subscriber can view according to HasPermissions trait
    $this->actingAs($user)
        ->getJson(route('admin.menus.index'))
        ->assertOk();

    // But cannot update
    $this->actingAs($user)
        ->putJson(route('admin.menus.update-structure', $menu), ['items' => []])
        ->assertForbidden();
});
