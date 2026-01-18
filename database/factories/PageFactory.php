<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Enums\PageType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(),
            'title' => fake()->sentence(),
            'excerpt' => fake()->paragraph(),
            'content' => [
                'blocks' => [
                    [
                        'type' => 'text',
                        'content' => fake()->paragraph(),
                    ],
                ],
            ],
            'meta' => [
                'description' => fake()->paragraph(),
                'keywords' => fake()->words(5),
            ],
            'featured_image' => fake()->imageUrl(800, 600, 'page', true),
            'status' => fake()->randomElement([PageStatus::Draft, PageStatus::Published]),
            'page_type' => fake()->randomElement([PageType::Static, PageType::Dynamic]),
            'author_id' => User::factory(),
            'template_id' => null,
            'published_at' => fake()->dateTimeBetween('-1 year', '+1 year'),
            'view_count' => fake()->numberBetween(0, 1000),
        ];
    }

    /**
     * Indicate that the page is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PageStatus::Published,
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the page is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PageStatus::Draft,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the page is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PageStatus::Archived,
        ]);
    }

    /**
     * Indicate that the page is a static page.
     */
    public function static(): static
    {
        return $this->state(fn (array $attributes) => [
            'page_type' => PageType::Static,
        ]);
    }

    /**
     * Indicate that the page is a dynamic page.
     */
    public function dynamic(): static
    {
        return $this->state(fn (array $attributes) => [
            'page_type' => PageType::Dynamic,
        ]);
    }

    /**
     * Indicate that the page has a template.
     */
    public function withTemplate(): static
    {
        return $this->state(fn (array $attributes) => [
            'template_id' => \App\Models\PageTemplate::factory(),
        ]);
    }
}
