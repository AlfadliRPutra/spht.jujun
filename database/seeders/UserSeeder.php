<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Idempotent — aman dijalankan berulang. Menggunakan updateOrCreate
     * sehingga TIDAK menghapus user lain (mis. pelanggan/petani yang sudah
     * mendaftar mandiri) dan akun admin selalu ada.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@spht.test'],
            [
                'name'        => 'Administrator',
                'password'    => Hash::make('password'),
                'role'        => 'admin',
                'no_hp'       => '081200000001',
                'alamat'      => 'Kantor SPHT Jujun',
                'is_verified' => true,
            ],
        );

        User::updateOrCreate(
            ['email' => 'pelanggan@spht.test'],
            [
                'name'          => 'Pelanggan Test',
                'password'      => Hash::make('password'),
                'role'          => 'pelanggan',
                'no_hp'         => '081200000002',
                'alamat'        => 'Jl. Mawar No. 1',
                // Wilayah default sebagai contoh siap-checkout untuk demo ongkir.
                'province_id'   => '12',
                'province_name' => 'Sumatera Utara',
                'city_id'       => '1271',
                'city_name'     => 'Kota Medan',
                'district_id'   => '127101',
                'district_name' => 'Medan Tuntungan',
                'is_verified'   => true,
            ],
        );
    }
}
