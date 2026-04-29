<?php

namespace App\Support;

/**
 * Pembungkus akses dataset wilayah administratif (province/city/district).
 *
 * Sumber data: config/wilayah.php (dataset simulasi terbatas).
 * Dipakai untuk mengisi dropdown form alamat & melakukan resolusi nama wilayah
 * berdasarkan ID yang dikirim form.
 */
class Wilayah
{
    public static function provinces(): array
    {
        $list = [];
        foreach (config('wilayah.provinces', []) as $pid => $province) {
            $list[] = ['id' => (string) $pid, 'name' => $province['name']];
        }
        return $list;
    }

    public static function provinceName(?string $provinceId): ?string
    {
        if (! $provinceId) {
            return null;
        }
        return config("wilayah.provinces.$provinceId.name");
    }

    public static function cityName(?string $provinceId, ?string $cityId): ?string
    {
        if (! $provinceId || ! $cityId) {
            return null;
        }
        return config("wilayah.provinces.$provinceId.cities.$cityId.name");
    }

    public static function districtName(?string $provinceId, ?string $cityId, ?string $districtId): ?string
    {
        if (! $provinceId || ! $cityId || ! $districtId) {
            return null;
        }
        return config("wilayah.provinces.$provinceId.cities.$cityId.districts.$districtId");
    }

    /**
     * Seluruh dataset dalam format JSON-friendly (untuk dipakai cascading select di JS).
     */
    public static function tree(): array
    {
        $tree = [];
        foreach (config('wilayah.provinces', []) as $pid => $province) {
            $cities = [];
            foreach ($province['cities'] ?? [] as $cid => $city) {
                $districts = [];
                foreach ($city['districts'] ?? [] as $did => $dname) {
                    $districts[] = ['id' => (string) $did, 'name' => $dname];
                }
                $cities[] = [
                    'id'        => (string) $cid,
                    'name'      => $city['name'],
                    'districts' => $districts,
                ];
            }
            $tree[] = [
                'id'     => (string) $pid,
                'name'   => $province['name'],
                'cities' => $cities,
            ];
        }
        return $tree;
    }

    public static function isValid(?string $provinceId, ?string $cityId, ?string $districtId): bool
    {
        return self::districtName($provinceId, $cityId, $districtId) !== null;
    }
}
