<?php

namespace App\Services;

use InvalidArgumentException;

/**
 * Simulasi ongkos kirim marketplace hasil tani lokal.
 *
 * Tidak memanggil API kurir eksternal (RajaOngkir/JNE/J&T/SiCepat).
 * Tarif murni ditentukan oleh kombinasi zona wilayah administratif
 * (province / kabupaten / kecamatan) antara alamat toko dan alamat pembeli,
 * dikalikan total berat barang per toko.
 *
 * Rumus: shipping_cost = base_fee + max(0, total_weight_kg - base_weight_kg) * extra_fee_per_kg
 */
class ShippingService
{
    public const ZONE_SAME_DISTRICT    = 'same_district';      // 1 kecamatan
    public const ZONE_SAME_CITY        = 'same_city';          // 1 kabupaten/kota, beda kecamatan
    public const ZONE_SAME_PROVINCE    = 'same_province';      // 1 provinsi, beda kabupaten/kota
    public const ZONE_OUTSIDE_PROVINCE = 'outside_province';   // luar provinsi

    /**
     * Tabel tarif per zona — terpusat agar mudah disesuaikan dan diuji.
     * Semua zona memakai formula yang sama (base + ekstra per kg di atas berat dasar);
     * yang membedakan hanya nominalnya.
     */
    private const RATES = [
        self::ZONE_SAME_DISTRICT => [
            'label'            => 'Satu Kecamatan',
            'base_fee'         => 7000,
            'base_weight_kg'   => 5,
            'extra_fee_per_kg' => 2000,
        ],
        self::ZONE_SAME_CITY => [
            'label'            => 'Satu Kabupaten/Kota',
            'base_fee'         => 20000,
            'base_weight_kg'   => 5,
            'extra_fee_per_kg' => 3000,
        ],
        self::ZONE_SAME_PROVINCE => [
            'label'            => 'Satu Provinsi',
            'base_fee'         => 25000,
            'base_weight_kg'   => 5,
            'extra_fee_per_kg' => 3000,
        ],
        self::ZONE_OUTSIDE_PROVINCE => [
            'label'            => 'Luar Provinsi',
            'base_fee'         => 30000,
            'base_weight_kg'   => 5,
            'extra_fee_per_kg' => 3000,
        ],
    ];

    /**
     * Hitung ongkos kirim dari satu toko ke alamat pembeli.
     *
     * @param  array  $storeAddress  Wajib mengandung province_id, city_id & district_id.
     * @param  array  $buyerAddress  Wajib mengandung province_id, city_id & district_id.
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
     * Tentukan zona berdasarkan kesamaan province/city/district antara toko dan pembeli.
     *
     * Pengecekan dilakukan bertingkat dari yang paling spesifik:
     * sama district → sama city → sama province → di luar province.
     */
    private function determineZone(array $store, array $buyer): string
    {
        $sameProvince = (string) $store['province_id'] === (string) $buyer['province_id'];
        $sameCity     = (string) $store['city_id']     === (string) $buyer['city_id'];
        $sameDistrict = (string) $store['district_id'] === (string) $buyer['district_id'];

        if ($sameProvince && $sameCity && $sameDistrict) {
            return self::ZONE_SAME_DISTRICT;
        }
        if ($sameProvince && $sameCity) {
            return self::ZONE_SAME_CITY;
        }
        if ($sameProvince) {
            return self::ZONE_SAME_PROVINCE;
        }
        return self::ZONE_OUTSIDE_PROVINCE;
    }

    private function assertAddress(string $label, array $address): void
    {
        foreach (['province_id', 'city_id', 'district_id'] as $field) {
            if (empty($address[$field])) {
                throw new InvalidArgumentException("$label.$field wajib diisi untuk perhitungan ongkir.");
            }
        }
    }

    private function buildMessage(string $zone, int $weight, array $rate, float $cost): string
    {
        $rp     = fn (float $n) => 'Rp '.number_format($n, 0, ',', '.');
        $detail = $rp($cost).' untuk '.$weight.' kg ('.$rate['label'].')';

        return match ($zone) {
            self::ZONE_SAME_DISTRICT    => 'Pengiriman dalam kecamatan: '.$detail,
            self::ZONE_SAME_CITY        => 'Pengiriman dalam kabupaten/kota: '.$detail,
            self::ZONE_SAME_PROVINCE    => 'Pengiriman dalam provinsi: '.$detail,
            self::ZONE_OUTSIDE_PROVINCE => 'Pengiriman antar provinsi: '.$detail,
        };
    }
}
