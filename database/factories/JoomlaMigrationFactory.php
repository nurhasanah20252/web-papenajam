<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\JoomlaMigration>
 */
class JoomlaMigrationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\JoomlaMigration>
     */
    protected $model = \App\Models\JoomlaMigration::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_table' => fake()->randomElement(['content', 'categories', 'menu', 'images', 'users']),
            'source_id' => fake()->numberBetween(1, 1000),
            'target_id' => fake()->numberBetween(1, 1000),
            'data_hash' => fake()->md5(),
            'migration_status' => fake()->randomElement(['pending', 'success', 'failed']),
            'error_message' => null,
            'migrated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the migration was successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'migration_status' => 'success',
            'error_message' => null,
        ]);
    }

    /**
     * Indicate that the migration failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'migration_status' => 'failed',
            'error_message' => fake()->sentence(),
        ]);
    }

    /**
     * Indicate that the migration is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'migration_status' => 'pending',
            'migrated_at' => null,
        ]);
    }
}
