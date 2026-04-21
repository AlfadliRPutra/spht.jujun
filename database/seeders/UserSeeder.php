<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name'        => 'Administrator',
            'email'       => 'admin@spht.test',
            'password'    => Hash::make('password'),
            'role'        => UserRole::Admin,
            'no_hp'       => '081200000001',
            'alamat'      => 'Kantor SPHT Jujun',
            'is_verified' => true,
        ]);

        User::factory()->petani()->count(3)->create();
        User::factory()->pelanggan()->count(5)->create();

        User::create([
            'name'        => 'Pelanggan Demo',
            'email'       => 'pelanggan@spht.test',
            'password'    => Hash::make('password'),
            'role'        => UserRole::Pelanggan,
            'no_hp'       => '081200000002',
            'alamat'      => 'Jl. Pelanggan No. 1',
            'is_verified' => false,
        ]);

        User::create([
            'name'        => 'Petani Demo',
            'email'       => 'petani@spht.test',
            'password'    => Hash::make('password'),
            'role'        => UserRole::Petani,
            'no_hp'       => '081200000003',
            'alamat'      => 'Jl. Sawah Raya No. 5',
            'is_verified' => true,
        ]);
    }
}
