<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
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
            'password' => Hash::make('password'),
            'role' => User::ROLE_USER,
            'is_active' => true,
            'access_scope' => User::ACCESS_SCOPE_SELECTED,
        ];
    }

    /**
     * Indicate that the user has the global ("all domains") Web Service scope.
     */
    public function withAllDomainsScope(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_scope' => User::ACCESS_SCOPE_ALL,
        ]);
    }

    /**
     * Indicate that the user has the "selected domains" Web Service scope.
     */
    public function withSelectedDomainsScope(): static
    {
        return $this->state(fn (array $attributes) => [
            'access_scope' => User::ACCESS_SCOPE_SELECTED,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function asAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => User::ROLE_ADMIN,
        ]);
    }

    /**
     * Indicate that the user is inactive.
     */
    public function asInactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
