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
        // Admin demo
        User::create([
            'name'        => 'Administrator',
            'email'       => 'admin@spht.test',
            'password'    => Hash::make('password'),
            'role'        => UserRole::Admin,
            'no_hp'       => '081200000001',
            'alamat'      => 'Kantor SPHT Jujun',
            'is_verified' => true,
        ]);

        // Petani demo — sudah terverifikasi lengkap
        User::create([
            'name'                      => 'Pak Budi (Demo)',
            'email'                     => 'petani@spht.test',
            'password'                  => Hash::make('password'),
            'role'                      => UserRole::Petani,
            'no_hp'                     => '081200000003',
            'alamat'                    => 'Jl. Sawah Raya No. 5, Desa Sukamaju',
            'nama_usaha'                => 'Kebun Makmur Jaya',
            'deskripsi_usaha'           => 'Kebun sayuran organik seluas 2 hektar, fokus pada sayur daun & sayur buah. Panen setiap minggu.',
            'nik'                       => '3201012345678901',
            'is_verified'               => true,
            'verification_submitted_at' => now()->subDays(30),
        ]);

        // Pelanggan demo
        User::create([
            'name'        => 'Pelanggan Demo',
            'email'       => 'pelanggan@spht.test',
            'password'    => Hash::make('password'),
            'role'        => UserRole::Pelanggan,
            'no_hp'       => '081200000002',
            'alamat'      => 'Jl. Pelanggan No. 1',
            'is_verified' => false,
        ]);

        // Petani acak — 2 terverifikasi, 1 pending review, 1 ditolak
        User::factory()->petani()->count(2)->create();
        User::factory()->petaniPending()->create();
        User::factory()->petaniRejected()->create();

        // Pelanggan acak
        User::factory()->pelanggan()->count(5)->create();
    }
}
