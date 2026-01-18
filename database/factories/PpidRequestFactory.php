<?php

namespace Database\Factories;

use App\Enums\PPIDPriority;
use App\Enums\PPIDStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PpidRequest>
 */
class PpidRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'request_number' => \App\Models\PpidRequest::generateRequestNumber(),
            'applicant_name' => fake()->name(),
            'nik' => fake()->regexify('[0-9]{16}'),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->safeEmail(),
            'request_type' => fake()->randomElement(['information', 'document', 'clarification']),
            'subject' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'status' => fake()->randomElement([PPIDStatus::Submitted, PPIDStatus::Reviewed]),
            'response' => null,
            'responded_at' => null,
            'processed_by' => null,
            'attachments' => [],
            'priority' => fake()->randomElement([PPIDPriority::Normal, PPIDPriority::High]),
            'notes' => [],
        ];
    }

    /**
     * Indicate that the request is submitted.
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PPIDStatus::Submitted,
        ]);
    }

    /**
     * Indicate that the request is reviewed.
     */
    public function reviewed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PPIDStatus::Reviewed,
        ]);
    }

    /**
     * Indicate that the request is processed.
     */
    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PPIDStatus::Processed,
        ]);
    }

    /**
     * Indicate that the request is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PPIDStatus::Completed,
            'response' => fake()->paragraph(),
            'responded_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'processed_by' => User::factory(),
        ]);
    }

    /**
     * Indicate that the request is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PPIDStatus::Rejected,
            'response' => fake()->paragraph(),
            'responded_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'processed_by' => User::factory(),
        ]);
    }

    /**
     * Indicate that the request is high priority.
     */
    public function highPriority(): static
    {
        return $this->state(fn (array $attributes) => [
            'priority' => PPIDPriority::High,
        ]);
    }

    /**
     * Indicate that the request has attachments.
     */
    public function withAttachments(): static
    {
        return $this->state(fn (array $attributes) => [
            'attachments' => [
                'document_1.pdf',
                'document_2.pdf',
            ],
        ]);
    }
}
