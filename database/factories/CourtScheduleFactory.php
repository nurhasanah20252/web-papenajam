<?php

namespace Database\Factories;

use App\Enums\ScheduleStatus;
use App\Enums\SyncStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CourtSchedule>
 */
class CourtScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'external_id' => null,
            'case_number' => fake()->regexify('[A-Z]{3}/[0-9]{4}/[A-Z]{2}/[0-9]{5}'),
            'case_title' => fake()->sentence(),
            'case_type' => fake()->randomElement(['Perdata', 'Pidana', 'Agama']),
            'judge_name' => fake()->name(),
            'court_room' => fake()->randomElement(['Ruang I', 'Ruang II', 'Ruang III']),
            'room_code' => fake()->randomElement(['R01', 'R02', 'R03']),
            'schedule_date' => fake()->dateTimeBetween('now', '+30 days'),
            'schedule_time' => fake()->time('H:i'),
            'schedule_status' => fake()->randomElement([ScheduleStatus::Scheduled, ScheduleStatus::Postponed]),
            'parties' => [
                'penggugat' => fake()->name(),
                'tergugat' => fake()->name(),
                'kuasa_hukum' => fake()->name(),
            ],
            'agenda' => fake()->sentence(),
            'notes' => fake()->sentence(),
            'last_sync_at' => null,
            'sync_status' => SyncStatus::Pending,
        ];
    }

    /**
     * Indicate that the schedule is from SIPP.
     */
    public function fromSipp(): static
    {
        return $this->state(fn (array $attributes) => [
            'external_id' => fake()->uuid(),
            'last_sync_at' => fake()->dateTimeBetween('-1 day', 'now'),
            'sync_status' => SyncStatus::Success,
        ]);
    }

    /**
     * Indicate that the schedule is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_status' => ScheduleStatus::Scheduled,
            'schedule_date' => fake()->dateTimeBetween('now', '+30 days'),
        ]);
    }

    /**
     * Indicate that the schedule is postponed.
     */
    public function postponed(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_status' => ScheduleStatus::Postponed,
        ]);
    }

    /**
     * Indicate that the schedule is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_status' => ScheduleStatus::Cancelled,
        ]);
    }

    /**
     * Indicate that the schedule is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_status' => ScheduleStatus::Completed,
            'schedule_date' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the schedule is today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_date' => now(),
            'schedule_time' => '10:00',
        ]);
    }

    /**
     * Indicate that the schedule is upcoming.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'schedule_date' => fake()->dateTimeBetween('+1 day', '+7 days'),
        ]);
    }
}
