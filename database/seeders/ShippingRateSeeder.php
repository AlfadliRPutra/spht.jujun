<?php

namespace Database\Seeders;

use App\Services\ShippingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShippingRateSeeder extends Seeder
{
    /**
     * Seed tarif ongkir awal — nilainya diambil dari konstanta default
     * yang sebelumnya ditanam di ShippingService::RATES. Admin bisa
     * mengubahnya kapan saja lewat menu "Tarif Ongkir".
     */
    public function run(): void
    {
        $rows = [
            [
                'zone'             => ShippingService::ZONE_SAME_DISTRICT,
                'label'            => 'Satu Kecamatan',
                'base_fee'         => 7000,
                'base_weight_kg'   => 5,
                'extra_fee_per_kg' => 2000,
            ],
            [
                'zone'             => ShippingService::ZONE_SAME_CITY,
                'label'            => 'Satu Kabupaten/Kota',
                'base_fee'         => 20000,
                'base_weight_kg'   => 5,
                'extra_fee_per_kg' => 3000,
            ],
            [
                'zone'             => ShippingService::ZONE_SAME_PROVINCE,
                'label'            => 'Satu Provinsi',
                'base_fee'         => 25000,
                'base_weight_kg'   => 5,
                'extra_fee_per_kg' => 3000,
            ],
            [
                'zone'             => ShippingService::ZONE_OUTSIDE_PROVINCE,
                'label'            => 'Luar Provinsi',
                'base_fee'         => 30000,
                'base_weight_kg'   => 5,
                'extra_fee_per_kg' => 3000,
            ],
        ];

        $now = now();

        foreach ($rows as $row) {
            DB::table('shipping_rates')->updateOrInsert(
                ['zone' => $row['zone']],
                $row + ['updated_at' => $now, 'created_at' => $now],
            );
        }
    }
}
