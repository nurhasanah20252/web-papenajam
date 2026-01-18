<?php

namespace Database\Factories;

use App\Enums\CaseTypeCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SippCaseType>
 */
class SippCaseTypeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\SippCaseType>
     */
    protected $model = \App\Models\SippCaseType::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->uuid(),
            'type_code' => fake()->regexify('[A-Z]{3}'),
            'type_name' => fake()->words(2, true),
            'category' => fake()->randomElement([CaseTypeCategory::Perdata, CaseTypeCategory::Pidana, CaseTypeCategory::Agama]),
            'legal_basis' => fake()->sentence(),
            'procedure_type' => fake()->randomElement(['biasa', 'cepat', 'khusus']),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the case type is perdata.
     */
    public function perdata(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => CaseTypeCategory::Perdata,
        ]);
    }

    /**
     * Indicate that the case type is pidana.
     */
    public function pidana(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => CaseTypeCategory::Pidana,
        ]);
    }

    /**
     * Indicate that the case type is agama.
     */
    public function agama(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => CaseTypeCategory::Agama,
        ]);
    }
}
