<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PageTemplate>
 */
class PageTemplateFactory extends Factory
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
            'description' => fake()->sentence(),
            'content' => [
                'blocks' => [
                    [
                        'type' => 'section',
                        'columns' => 2,
                    ],
                ],
            ],
            'is_system' => fake()->boolean(20),
            'thumbnail' => fake()->imageUrl(400, 300, 'template', true),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the template is a system template.
     */
    public function system(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => true,
        ]);
    }

    /**
     * Indicate that the template is a custom template.
     */
    public function custom(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_system' => false,
        ]);
    }
}
