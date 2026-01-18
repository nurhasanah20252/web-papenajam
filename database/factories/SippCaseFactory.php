<?php

namespace Database\Factories;

use App\Enums\CaseStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SippCase>
 */
class SippCaseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\SippCase>
     */
    protected $model = \App\Models\SippCase::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->uuid(),
            'case_number' => fake()->regexify('[A-Z]{3}/[0-9]{4}/[A-Z]{2}/[0-9]{5}'),
            'case_title' => fake()->sentence(),
            'case_type' => fake()->randomElement(['Perdata', 'Pidana', 'Agama']),
            'register_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'register_number' => fake()->regexify('[0-9]{5}/[A-Z]{3}/[0-9]{4}'),
            'case_status' => fake()->randomElement([CaseStatus::Pending, CaseStatus::InProgress]),
            'priority' => fake()->randomElement(['normal', 'high', 'urgent']),
            'plaintiff' => [
                ['name' => fake()->name(), 'address' => fake()->address()],
            ],
            'defendant' => [
                ['name' => fake()->name(), 'address' => fake()->address()],
            ],
            'attorney' => [
                ['name' => fake()->name(), 'firm' => fake()->company()],
            ],
            'subject_matter' => fake()->sentence(),
            'last_hearing_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'next_hearing_date' => fake()->dateTimeBetween('now', '+30 days'),
            'final_decision_date' => null,
            'decision_summary' => null,
            'document_references' => [],
            'last_sync_at' => fake()->dateTimeBetween('-1 day', 'now'),
            'sync_status' => 'success',
        ];
    }

    /**
     * Indicate that the case is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'case_status' => CaseStatus::Pending,
        ]);
    }

    /**
     * Indicate that the case is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'case_status' => CaseStatus::InProgress,
        ]);
    }

    /**
     * Indicate that the case is closed.
     */
    public function closed(): static
    {
        return $this->state(fn (array $attributes) => [
            'case_status' => CaseStatus::Closed,
            'final_decision_date' => fake()->dateTimeBetween('-30 days', 'now'),
            'decision_summary' => fake()->paragraph(),
        ]);
    }
}
