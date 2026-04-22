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
            'no_hp'             => fake()->numerify('08##########'),
            'alamat'            => fake()->address(),
            'is_verified'       => false,
            'remember_token'    => Str::random(10),
        ];
    }

    public function petani(): static
    {
        return $this->state(fn () => [
            'role'                      => UserRole::Petani,
            'is_verified'               => true,
            'nama_usaha'                => 'Kebun '.fake()->lastName(),
            'deskripsi_usaha'           => fake()->sentence(14),
            'nik'                       => fake()->numerify('################'),
            'verification_submitted_at' => now()->subDays(fake()->numberBetween(7, 60)),
        ]);
    }

    public function petaniPending(): static
    {
        return $this->state(fn () => [
            'role'                      => UserRole::Petani,
            'is_verified'               => false,
            'nama_usaha'                => 'Tani '.fake()->lastName(),
            'deskripsi_usaha'           => fake()->sentence(14),
            'nik'                       => fake()->numerify('################'),
            'verification_submitted_at' => now()->subDays(fake()->numberBetween(1, 5)),
        ]);
    }

    public function petaniRejected(): static
    {
        return $this->state(fn () => [
            'role'                      => UserRole::Petani,
            'is_verified'               => false,
            'nama_usaha'                => 'Tani '.fake()->lastName(),
            'deskripsi_usaha'           => fake()->sentence(14),
            'nik'                       => fake()->numerify('################'),
            'verification_submitted_at' => null,
            'verification_note'         => 'Foto KTP buram, mohon unggah ulang dengan pencahayaan cukup.',
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
