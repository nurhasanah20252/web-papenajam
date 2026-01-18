<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserActivityLog>
 */
class UserActivityLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Models\UserActivityLog>
     */
    protected $model = \App\Models\UserActivityLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['login', 'logout', 'create', 'update', 'delete']),
            'description' => fake()->sentence(),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'metadata' => [
                'url' => fake()->url(),
                'method' => fake()->randomElement(['GET', 'POST', 'PUT', 'DELETE']),
            ],
        ];
    }

    /**
     * Indicate that the action is login.
     */
    public function login(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'login',
            'description' => 'User logged in',
        ]);
    }

    /**
     * Indicate that the action is logout.
     */
    public function logout(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'logout',
            'description' => 'User logged out',
        ]);
    }

    /**
     * Indicate that the action is create.
     */
    public function actionCreate(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'create',
        ]);
    }

    /**
     * Indicate that the action is update.
     */
    public function actionUpdate(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'update',
        ]);
    }

    /**
     * Indicate that the action is delete.
     */
    public function actionDelete(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'delete',
        ]);
    }
}
