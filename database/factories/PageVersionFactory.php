<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PageVersion>
 */
class PageVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_id' => \App\Models\Page::factory(),
            'version' => 1,
            'content' => ['content' => fake()->paragraph()],
            'builder_content' => [
                ['type' => 'text', 'content' => fake()->sentence()],
            ],
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
