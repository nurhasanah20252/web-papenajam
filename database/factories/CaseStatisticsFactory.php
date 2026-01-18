<?php

namespace Database\Factories;

use App\Enums\CaseTypeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CaseStatistics>
 */
class CaseStatisticsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\CaseStatistics>
     */
    protected $model = \App\Models\CaseStatistics::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalFiled = fake()->numberBetween(50, 500);
        $totalResolved = fake()->numberBetween(20, $totalFiled);
        $pendingCarryover = fake()->numberBetween(10, 100);

        return [
            'year' => fake()->year(),
            'month' => fake()->month(),
            'case_type' => fake()->randomElement(['Perdata', 'Pidana', 'Agama']),
            'court_type' => fake()->randomElement([CaseTypeCategory::Perdata, CaseTypeCategory::Pidana, CaseTypeCategory::Agama]),
            'total_filed' => $totalFiled,
            'total_resolved' => $totalResolved,
            'pending_carryover' => $pendingCarryover,
            'avg_resolution_days' => fake()->numberBetween(30, 365),
            'settlement_rate' => fake()->randomFloat(2, 0, 100),
            'external_data_hash' => fake()->md5(),
            'last_sync_at' => fake()->dateTimeBetween('-1 day', 'now'),
        ];
    }

    /**
     * Indicate that the statistics are for the current month.
     */
    public function currentMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => now()->year,
            'month' => now()->month,
        ]);
    }

    /**
     * Indicate that the statistics are for perdata cases.
     */
    public function perdata(): static
    {
        return $this->state(fn (array $attributes) => [
            'case_type' => 'Perdata',
            'court_type' => CaseTypeCategory::Perdata,
        ]);
    }

    /**
     * Indicate that the statistics are for pidana cases.
     */
    public function pidana(): static
    {
        return $this->state(fn (array $attributes) => [
            'case_type' => 'Pidana',
            'court_type' => CaseTypeCategory::Pidana,
        ]);
    }

    /**
     * Indicate that the statistics are for agama cases.
     */
    public function agama(): static
    {
        return $this->state(fn (array $attributes) => [
            'case_type' => 'Agama',
            'court_type' => CaseTypeCategory::Agama,
        ]);
    }
}
