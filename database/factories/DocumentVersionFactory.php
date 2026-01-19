<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentVersion>
 */
class DocumentVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'version' => '1.0.'.fake()->numberBetween(0, 9),
            'file_path' => 'documents/versions/'.fake()->uuid().'.pdf',
            'file_name' => fake()->words(3, true).'.pdf',
            'file_size' => fake()->numberBetween(1024, 10485760),
            'file_type' => 'application/pdf',
            'mime_type' => 'application/pdf',
            'checksum' => fake()->sha256(),
            'changelog' => fake()->sentence(),
            'created_by' => User::factory(),
            'is_current' => fake()->boolean(20),
        ];
    }

    /**
     * Indicate that the version is current.
     */
    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => true,
        ]);
    }
}
