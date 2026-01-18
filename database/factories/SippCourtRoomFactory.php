<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SippCourtRoom>
 */
class SippCourtRoomFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\SippCourtRoom>
     */
    protected $model = \App\Models\SippCourtRoom::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => fake()->uuid(),
            'room_code' => fake()->regexify('R[0-9]{2}'),
            'room_name' => fake()->randomElement(['Ruang I', 'Ruang II', 'Ruang III', 'Ruang IV']),
            'building' => fake()->randomElement(['Gedung A', 'Gedung B', 'Gedung C']),
            'capacity' => fake()->numberBetween(20, 100),
            'facilities' => [
                'projector' => fake()->boolean(80),
                'ac' => fake()->boolean(90),
                'sound_system' => fake()->boolean(70),
            ],
            'is_active' => true,
            'last_sync_at' => fake()->dateTimeBetween('-1 day', 'now'),
        ];
    }

    /**
     * Indicate that the room is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the room is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
