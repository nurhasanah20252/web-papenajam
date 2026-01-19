<?php

namespace Database\Factories;

use App\Enums\MenuLocation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Menu>
 */
class MenuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $location = fake()->randomElement([MenuLocation::Header, MenuLocation::Footer, MenuLocation::Sidebar, MenuLocation::Mobile]);

        return [
            'name' => fake()->words(2, true),
            'location' => $location,
            'locations' => [$location->value],
            'max_depth' => fake()->numberBetween(2, 4),
            'description' => fake()->sentence(),
        ];
    }

    /**
     * Indicate that the menu is in the header.
     */
    public function header(): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => MenuLocation::Header,
        ]);
    }

    /**
     * Indicate that the menu is in the footer.
     */
    public function footer(): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => MenuLocation::Footer,
        ]);
    }

    /**
     * Indicate that the menu is in the sidebar.
     */
    public function sidebar(): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => MenuLocation::Sidebar,
        ]);
    }

    /**
     * Indicate that the menu is for mobile.
     */
    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'location' => MenuLocation::Mobile,
        ]);
    }
}
