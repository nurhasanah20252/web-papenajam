<?php

namespace Database\Factories;

use App\Enums\NewsStatus;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\News>
 */
class NewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'excerpt' => fake()->paragraph(),
            'content' => fake()->paragraphs(3, true),
            'featured_image' => fake()->imageUrl(800, 600, 'news', true),
            'is_featured' => fake()->boolean(20),
            'views_count' => fake()->numberBetween(0, 5000),
            'category_id' => Category::factory()->news(),
            'author_id' => User::factory(),
            'status' => fake()->randomElement([NewsStatus::Draft, NewsStatus::Published]),
            'published_at' => fake()->dateTimeBetween('-1 year', '+1 year'),
            'tags' => fake()->words(3),
        ];
    }

    /**
     * Indicate that the news is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => NewsStatus::Published,
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Indicate that the news is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => NewsStatus::Draft,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the news is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the news has many views.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'views_count' => fake()->numberBetween(1000, 10000),
            'is_featured' => true,
        ]);
    }
}
