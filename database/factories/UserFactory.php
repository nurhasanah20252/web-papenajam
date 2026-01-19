<?php

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => UserRole::Author,
            'permissions' => [],
            'last_login_at' => null,
            'profile_completed' => false,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model has two-factor authentication configured.
     */
    public function withTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => encrypt('secret'),
            'two_factor_recovery_codes' => encrypt(json_encode(['recovery-code-1'])),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    /**
     * Indicate that the user is a super admin.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::SuperAdmin,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin,
        ]);
    }

    /**
     * Indicate that the user is an author.
     */
    public function author(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Author,
        ]);
    }

    /**
     * Indicate that the user is a designer.
     */
    public function designer(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Designer,
        ]);
    }

    /**
     * Indicate that the user is a subscriber.
     */
    public function subscriber(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Subscriber,
        ]);
    }

    /**
     * Indicate that the user has completed their profile.
     */
    public function profileCompleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_completed' => true,
        ]);
    }

    /**
     * Indicate that the user has logged in.
     */
    public function loggedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_login_at' => now(),
        ]);
    }
}
