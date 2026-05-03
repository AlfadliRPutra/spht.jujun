<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProvinceSeeder::class,
            RegencySeeder::class,
            DistrictSeeder::class,
            UserSeeder::class,
            HeroSeeder::class,
            ShippingRateSeeder::class,
            OrderSeeder::class,
        ]);

        // CategorySeeder & ProductSeeder dijalankan manual:
        //   php artisan db:seed --class=CategorySeeder
        //   php artisan db:seed --class=ProductSeeder
    }
}
