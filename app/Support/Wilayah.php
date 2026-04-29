<?php

namespace App\Support;

use App\Models\District;
use App\Models\Province;
use App\Models\Regency;

/**
 * Pembungkus akses dataset wilayah administratif (province / regency / district).
 *
 * Sumber data: tabel provinces / regencies / districts (lihat WilayahSeeder).
 * Hasil query di-cache di memori per-request supaya satu render Blade tidak
 * mengeluarkan ratusan query (mis. dropdown + JS lookup nama wilayah).
 */
class Wilayah
{
    /** @var array<string, array<int, array{id:string,name:string}>>|null */
    private static array $citiesByProvince = [];

    /** @var array<string, array<int, array{id:string,name:string}>>|null */
    private static array $districtsByCity = [];

    private static ?array $provinceNameById = null;
    private static array $cityNameById      = [];
    private static array $districtNameById  = [];

    /** @return array<int, array{id:string,name:string}> */
    public static function provinces(): array
    {
        return Province::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Province $p) => ['id' => (string) $p->id, 'name' => $p->name])
            ->all();
    }

    /** @return array<int, array{id:string,name:string}> */
    public static function cities(?string $provinceId): array
    {
        if (! $provinceId) {
            return [];
        }
        if (! array_key_exists($provinceId, self::$citiesByProvince)) {
            self::$citiesByProvince[$provinceId] = Regency::query()
                ->where('province_id', $provinceId)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Regency $r) => ['id' => (string) $r->id, 'name' => $r->name])
                ->all();
        }
        return self::$citiesByProvince[$provinceId];
    }

    /** @return array<int, array{id:string,name:string}> */
    public static function districts(?string $cityId): array
    {
        if (! $cityId) {
            return [];
        }
        if (! array_key_exists($cityId, self::$districtsByCity)) {
            self::$districtsByCity[$cityId] = District::query()
                ->where('regency_id', $cityId)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (District $d) => ['id' => (string) $d->id, 'name' => $d->name])
                ->all();
        }
        return self::$districtsByCity[$cityId];
    }

    public static function provinceName(?string $provinceId): ?string
    {
        if (! $provinceId) {
            return null;
        }
        if (self::$provinceNameById === null) {
            self::$provinceNameById = Province::query()->pluck('name', 'id')->all();
        }
        return self::$provinceNameById[$provinceId] ?? null;
    }

    public static function cityName(?string $provinceId, ?string $cityId): ?string
    {
        if (! $cityId) {
            return null;
        }
        if (! array_key_exists($cityId, self::$cityNameById)) {
            $row = Regency::query()
                ->where('id', $cityId)
                ->when($provinceId, fn ($q) => $q->where('province_id', $provinceId))
                ->value('name');
            self::$cityNameById[$cityId] = $row;
        }
        return self::$cityNameById[$cityId];
    }

    public static function districtName(?string $provinceId, ?string $cityId, ?string $districtId): ?string
    {
        if (! $districtId) {
            return null;
        }
        if (! array_key_exists($districtId, self::$districtNameById)) {
            $row = District::query()
                ->where('id', $districtId)
                ->when($cityId, fn ($q) => $q->where('regency_id', $cityId))
                ->value('name');
            self::$districtNameById[$districtId] = $row;
        }
        return self::$districtNameById[$districtId];
    }

    /**
     * Validasi rantai province → city → district. Lebih murah daripada
     * memuat seluruh tree karena hanya butuh 1 query bertabel join sederhana.
     */
    public static function isValid(?string $provinceId, ?string $cityId, ?string $districtId): bool
    {
        if (! $provinceId || ! $cityId || ! $districtId) {
            return false;
        }
        return District::query()
            ->where('districts.id', $districtId)
            ->where('districts.regency_id', $cityId)
            ->whereHas('regency', fn ($q) => $q->where('province_id', $provinceId))
            ->exists();
    }
}
