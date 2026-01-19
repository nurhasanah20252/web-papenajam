<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'file_path' => 'documents/'.fake()->uuid().'.pdf',
            'file_name' => fake()->words(3, true).'.pdf',
            'file_size' => fake()->numberBetween(1024, 10485760),
            'file_type' => 'application/pdf',
            'mime_type' => 'application/pdf',
            'category_id' => Category::factory()->document(),
            'uploaded_by' => User::factory(),
            'download_count' => fake()->numberBetween(0, 1000),
            'is_public' => fake()->boolean(80),
            'published_at' => fake()->dateTimeBetween('-1 year', '+1 year'),
            'version' => '1.0.'.fake()->numberBetween(0, 9),
            'checksum' => fake()->md5(),
            'tags' => fake()->words(3),
            'allowed_roles' => null,
        ];
    }

    /**
     * Indicate that the document is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the document is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }

    /**
     * Indicate that the document has many downloads.
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'download_count' => fake()->numberBetween(100, 10000),
        ]);
    }
}
