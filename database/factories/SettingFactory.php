<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => fake()->randomElement(['general', 'site', 'seo', 'social']),
            'key' => fake()->unique()->word(),
            'value' => fake()->sentence(),
            'type' => fake()->randomElement(['text', 'boolean', 'integer', 'json']),
            'is_public' => fake()->boolean(),
        ];
    }
}
