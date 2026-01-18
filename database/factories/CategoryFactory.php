<?php

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->unique()->slug(),
            'description' => fake()->sentence(),
            'parent_id' => null,
            'type' => fake()->randomElement([CategoryType::News, CategoryType::Document]),
            'icon' => fake()->randomElement(['folder', 'file', 'news', null]),
            'order' => fake()->numberBetween(1, 50),
        ];
    }

    /**
     * Indicate that the category is for news.
     */
    public function news(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CategoryType::News,
        ]);
    }

    /**
     * Indicate that the category is for documents.
     */
    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CategoryType::Document,
        ]);
    }

    /**
     * Indicate that the category is for pages.
     */
    public function page(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CategoryType::Page,
        ]);
    }

    /**
     * Indicate that the category is for budget.
     */
    public function budget(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CategoryType::Budget,
        ]);
    }

    /**
     * Indicate that the category has a parent.
     */
    public function withParent(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => Category::factory(),
        ]);
    }

    /**
     * Indicate that the category has children.
     */
    public function withChildren(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ])->afterCreating(function ($category) {
            Category::factory()->count(3)->create([
                'parent_id' => $category->id,
                'type' => $category->type,
            ]);
        });
    }
}
