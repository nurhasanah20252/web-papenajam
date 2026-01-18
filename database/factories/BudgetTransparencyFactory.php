<?php

namespace Database\Factories;

use App\Enums\BudgetCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BudgetTransparency>
 */
class BudgetTransparencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'year' => fake()->year(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'amount' => fake()->numberBetween(100000000, 10000000000),
            'document_path' => 'budget/' . fake()->uuid() . '.pdf',
            'document_name' => fake()->words(3, true) . '.pdf',
            'category' => fake()->randomElement([BudgetCategory::APBN, BudgetCategory::APBD]),
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'author_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the budget is APBN.
     */
    public function apbn(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => BudgetCategory::APBN,
        ]);
    }

    /**
     * Indicate that the budget is APBD.
     */
    public function apbd(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => BudgetCategory::APBD,
        ]);
    }

    /**
     * Indicate that the budget is for the current year.
     */
    public function currentYear(): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => now()->year,
        ]);
    }
}
