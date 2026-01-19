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
        $urlType = fake()->randomElement([UrlType::Route, UrlType::Page, UrlType::Custom]);

        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'title' => fake()->words(2, true),
            'url_type' => $urlType,
            'type' => $urlType->value,
            'route_name' => null,
            'page_id' => null,
            'custom_url' => $urlType === UrlType::Custom ? fake()->url() : null,
            'icon' => fake()->randomElement(['home', 'user', 'document', 'calendar', null]),
            'class_name' => null,
            'order' => fake()->numberBetween(1, 100),
            'target_blank' => fake()->boolean(20),
            'target' => '_self',
            'is_active' => true,
            'conditions' => [],
            'display_rules' => [],
        ];
    }

    /**
     * Indicate that the menu item is a route.
     */
    public function withRoute(): static
    {
        return $this->state(fn (array $attributes) => [
            'url_type' => UrlType::Route,
            'type' => UrlType::Route->value,
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
            'type' => UrlType::Page->value,
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
            'type' => UrlType::External->value,
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
