<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Hapus semua data user terlebih dahulu
        User::truncate();

        User::create([
            'name'        => 'Administrator',
            'email'       => 'admin@spht.test',
            'password'    => bcrypt('password'),
            'role'        => 'admin',
            'no_hp'       => '081200000001',
            'alamat'      => 'Kantor SPHT Jujun',
            'is_verified' => true,
        ]);

        // Tambahkan user lain jika ada
        User::create([
            'name'        => 'Pelanggan Test',
            'email'       => 'pelanggan@spht.test',
            'password'    => bcrypt('password'),
            'role'        => 'pelanggan',
            'no_hp'       => '081200000002',
            'alamat'      => 'Alamat Pelanggan',
            'is_verified' => true,
        ]);



        // Re-enable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}