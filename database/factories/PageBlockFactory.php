<?php

namespace Database\Factories;

use App\Enums\BlockType;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PageBlock>
 */
class PageBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'type' => fake()->randomElement([BlockType::Text, BlockType::Image, BlockType::Html]),
            'content' => [
                'text' => fake()->paragraph(),
            ],
            'settings' => [
                'alignment' => fake()->randomElement(['left', 'center', 'right']),
                'padding' => fake()->numberBetween(0, 20),
            ],
            'order' => fake()->numberBetween(1, 10),
        ];
    }

    /**
     * Indicate that the block is text.
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BlockType::Text,
            'content' => ['text' => fake()->paragraph()],
        ]);
    }

    /**
     * Indicate that the block is an image.
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BlockType::Image,
            'content' => ['src' => fake()->imageUrl(800, 600)],
        ]);
    }

    /**
     * Indicate that the block is HTML.
     */
    public function html(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => BlockType::Html,
            'content' => ['html' => '<div>'.fake()->paragraph().'</div>'],
        ]);
    }
}
