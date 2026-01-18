<?php

namespace Database\Factories;

use App\Enums\UrlType;
use App\Models\Menu;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'title' => fake()->words(2, true),
            'url_type' => fake()->randomElement([UrlType::Route, UrlType::Page, UrlType::Custom]),
            'route_name' => null,
            'page_id' => null,
            'custom_url' => fake()->url(),
            'icon' => fake()->randomElement(['home', 'user', 'document', 'calendar', null]),
            'order' => fake()->numberBetween(1, 100),
            'target_blank' => fake()->boolean(20),
            'is_active' => true,
            'conditions' => [],
        ];
    }

    /**
     * Indicate that the menu item is a route.
     */
    public function withRoute(): static
    {
        return $this->state(fn (array $attributes) => [
            'url_type' => UrlType::Route,
            'route_name' => fake()->randomElement(['home', 'about', 'contact']),
            'custom_url' => null,
            'page_id' => null,
        ]);
    }

    /**
     * Indicate that the menu item links to a page.
     */
    public function withPage(): static
    {
        return $this->state(fn (array $attributes) => [
            'url_type' => UrlType::Page,
            'page_id' => Page::factory(),
            'route_name' => null,
            'custom_url' => null,
        ]);
    }

    /**
     * Indicate that the menu item is custom.
     */
    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'url_type' => UrlType::Custom,
            'custom_url' => fake()->url(),
            'route_name' => null,
            'page_id' => null,
        ]);
    }

    /**
     * Indicate that the menu item is external.
     */
    public function external(): static
    {
        return $this->state(fn (array $attributes) => [
            'url_type' => UrlType::External,
            'custom_url' => fake()->url(),
            'route_name' => null,
            'page_id' => null,
            'target_blank' => true,
        ]);
    }

    /**
     * Indicate that the menu item is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the menu item has children.
     */
    public function withChildren(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ])->afterCreating(function ($menuItem) {
            MenuItem::factory()->count(3)->create([
                'menu_id' => $menuItem->menu_id,
                'parent_id' => $menuItem->id,
            ]);
        });
    }
}
