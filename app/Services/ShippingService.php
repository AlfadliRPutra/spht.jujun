<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Simulasi ongkos kirim marketplace hasil tani lokal.
 *
 * Tidak memanggil API kurir eksternal (RajaOngkir/JNE/J&T/SiCepat).
 * Tarif murni ditentukan oleh kombinasi zona wilayah administratif
 * (kecamatan / kota-kabupaten) dan total berat barang per toko.
 *
 * Rumus: shipping_cost = base_fee + max(0, total_weight_kg - 5) * extra_fee_per_kg
 */
class ShippingService
{
    public const ZONE_SAME_DISTRICT = 'same_district';
    public const ZONE_SAME_CITY     = 'same_city';
    public const ZONE_OUTSIDE_CITY  = 'outside_city';

    /**
     * Tabel tarif per zona — terpusat agar mudah disesuaikan dan diuji.
     */
    private const RATES = [
        self::ZONE_SAME_DISTRICT => [
            'label'            => 'Satu Kecamatan',
            'base_fee'         => 10000,
            'base_weight_kg'   => 5,
            'extra_fee_per_kg' => 2000,
        ],
        self::ZONE_SAME_CITY => [
            'label'            => 'Satu Kota/Kabupaten',
            'base_fee'         => 20000,
            'base_weight_kg'   => 5,
            'extra_fee_per_kg' => 3000,
        ],
        self::ZONE_OUTSIDE_CITY => [
            'label'            => 'Luar Kota/Kabupaten',
            'base_fee'         => 0,
            'base_weight_kg'   => 5,
            'extra_fee_per_kg' => 0,
        ],
    ];

    /**
     * Hitung ongkos kirim dari satu toko ke alamat pembeli.
     *
     * @param  array  $storeAddress  Wajib mengandung city_id & district_id (lihat User::addressSnapshot()).
     * @param  array  $buyerAddress  Wajib mengandung city_id & district_id.
     * @param  float  $totalWeightKg Total berat untuk toko ini (kg). Pecahan dibulatkan ke atas.
     * @return array  Lihat dokumentasi return format pada README/spec fitur.
     */
    public function calculateShipping(array $storeAddress, array $buyerAddress, float $totalWeightKg): array
    {
        $this->assertAddress('storeAddress', $storeAddress);
        $this->assertAddress('buyerAddress', $buyerAddress);

        if ($totalWeightKg <= 0) {
            throw new InvalidArgumentException('totalWeightKg harus lebih dari 0.');
        }

        // Berat dibulatkan ke atas — kurir umumnya menagih per-kg utuh.
        $weight = (int) ceil($totalWeightKg);

        $zone = $this->determineZone($storeAddress, $buyerAddress);

        if ($zone === self::ZONE_OUTSIDE_CITY) {
            // Luar kota/kabupaten: pengiriman tidak tersedia, checkout toko harus diblok.
            return [
                'available'        => false,
                'zone'             => $zone,
                'zone_label'       => self::RATES[$zone]['label'],
                'base_fee'         => 0,
                'base_weight_kg'   => self::RATES[$zone]['base_weight_kg'],
                'extra_fee_per_kg' => 0,
                'total_weight_kg'  => $weight,
                'shipping_cost'    => 0,
                'message'          => 'Pengiriman tidak tersedia: toko berada di luar kota/kabupaten Anda.',
            ];
        }

        $rate = self::RATES[$zone];

        // Komponen tambahan hanya berlaku untuk berat di atas berat dasar.
        $extraWeight  = max(0, $weight - $rate['base_weight_kg']);
        $shippingCost = $rate['base_fee'] + ($extraWeight * $rate['extra_fee_per_kg']);

        return [
            'available'        => true,
            'zone'             => $zone,
            'zone_label'       => $rate['label'],
            'base_fee'         => (int) $rate['base_fee'],
            'base_weight_kg'   => (int) $rate['base_weight_kg'],
            'extra_fee_per_kg' => (int) $rate['extra_fee_per_kg'],
            'total_weight_kg'  => $weight,
            'shipping_cost'    => (int) $shippingCost,
            'message'          => $this->buildMessage($zone, $weight, $rate, $shippingCost),
        ];
    }

    /**
     * Tentukan zona berdasarkan kesamaan city_id / district_id antara toko dan pembeli.
     */
    private function determineZone(array $store, array $buyer): string
    {
        $sameCity     = (string) $store['city_id']     === (string) $buyer['city_id'];
        $sameDistrict = (string) $store['district_id'] === (string) $buyer['district_id'];

        if ($sameCity && $sameDistrict) {
            return self::ZONE_SAME_DISTRICT;
        }
        if ($sameCity) {
            return self::ZONE_SAME_CITY;
        }
        return self::ZONE_OUTSIDE_CITY;
    }

    private function assertAddress(string $label, array $address): void
    {
        foreach (['city_id', 'district_id'] as $field) {
            if (empty($address[$field])) {
                throw new InvalidArgumentException("$label.$field wajib diisi untuk perhitungan ongkir.");
            }
        }
    }

    private function buildMessage(string $zone, int $weight, array $rate, float $cost): string
    {
        $rp     = fn (float $n) => 'Rp '.number_format($n, 0, ',', '.');
        $detail = $rp($cost).' untuk '.$weight.' kg ('.$rate['label'].')';

        return $zone === self::ZONE_SAME_DISTRICT
            ? 'Pengiriman dalam kecamatan: '.$detail
            : 'Pengiriman dalam kota/kabupaten: '.$detail;
    }
}
