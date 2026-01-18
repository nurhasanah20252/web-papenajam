<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SippJudge>
 */
class SippJudgeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\SippJudge>
     */
    protected $model = \App\Models\SippJudge::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->uuid(),
            'judge_code' => fake()->regexify('[A-Z]{3}[0-9]{4}'),
            'full_name' => fake()->name(),
            'title' => fake()->randomElement(['Yang Mulia', 'Dr.', 'S.H.', 'M.H.']),
            'specialization' => fake()->randomElement(['Perdata', 'Pidana', 'Agama']),
            'chamber' => fake()->randomElement(['I', 'II', 'III']),
            'is_active' => true,
            'last_sync_at' => fake()->dateTimeBetween('-1 day', 'now'),
        ];
    }

    /**
     * Indicate that the judge is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the judge is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
