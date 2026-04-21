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
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'role'              => UserRole::Pelanggan,
            'no_hp'             => fake()->phoneNumber(),
            'alamat'            => fake()->address(),
            'is_verified'       => false,
            'remember_token'    => Str::random(10),
        ];
    }

    public function petani(): static
    {
        return $this->state(fn () => [
            'role'        => UserRole::Petani,
            'is_verified' => true,
        ]);
    }

    public function pelanggan(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::Pelanggan,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role'        => UserRole::Admin,
            'is_verified' => true,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
            'is_verified'       => false,
        ]);
    }
}
