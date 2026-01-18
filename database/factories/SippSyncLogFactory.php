<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SippSyncLog>
 */
class SippSyncLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\SippSyncLog>
     */
    protected $model = \App\Models\SippSyncLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sync_type' => fake()->randomElement(['full', 'incremental']),
            'start_time' => fake()->dateTimeBetween('-1 hour', 'now'),
            'end_time' => fake()->dateTimeBetween('now', '+1 hour'),
            'records_fetched' => fake()->numberBetween(10, 1000),
            'records_updated' => fake()->numberBetween(0, 500),
            'records_created' => fake()->numberBetween(0, 500),
            'error_message' => null,
            'created_by' => fake()->randomElement(['system', 'user']),
            'metadata' => [
                'endpoint' => fake()->url(),
                'duration' => fake()->numberBetween(1, 300),
            ],
        ];
    }

    /**
     * Indicate that the sync was successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'error_message' => null,
        ]);
    }

    /**
     * Indicate that the sync failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'error_message' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the sync was full.
     */
    public function full(): static
    {
        return $this->state(fn (array $attributes) => [
            'sync_type' => 'full',
        ]);
    }

    /**
     * Indicate that the sync was incremental.
     */
    public function incremental(): static
    {
        return $this->state(fn (array $attributes) => [
            'sync_type' => 'incremental',
        ]);
    }
}
