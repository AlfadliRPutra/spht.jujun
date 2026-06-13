<?php

namespace App\Support;

/**
 * Format nilai berat (selalu disimpan dalam kg di DB) menjadi string yang
 * paling natural dibaca user awam — gram untuk barang kecil, kg untuk
 * barang sedang, kuintal untuk barang sangat besar.
 *
 * Sengaja minimal: tidak generate "ons" karena di display read-only,
 * "ons" lebih jarang digunakan di e-commerce dibanding pasar tradisional.
 * Petani tetap bisa input pakai ons di form (lihat weight-picker-script).
 *
 * Contoh:
 *   0.001  → "1 gram"
 *   0.05   → "50 gram"
 *   0.5    → "500 gram"
 *   1      → "1 kg"
 *   1.5    → "1,5 kg"
 *   1.234  → "1,234 kg"
 *   100    → "1 kuintal"
 *   250.5  → "2,505 kuintal"
 */
class WeightFormatter
{
    public static function humanize(float|int|null $kg): string
    {
        $kg = (float) ($kg ?? 0);

        if ($kg <= 0) {
            return '0 gram';
        }

        if ($kg >= 100) {
            return self::trimDecimal($kg / 100).' kuintal';
        }

        if ($kg >= 1) {
            return self::trimDecimal($kg).' kg';
        }

        // < 1 kg → tampilkan dalam gram (integer)
        $gram = (int) round($kg * 1000);
        return number_format($gram, 0, ',', '.').' gram';
    }

    /**
     * Format desimal id-ID, buang trailing zero & koma menggantung.
     */
    private static function trimDecimal(float $n, int $maxDecimals = 3): string
    {
        $s = number_format($n, $maxDecimals, ',', '.');
        // 1,500 → 1,5 ; 1,000 → 1
        return rtrim(rtrim($s, '0'), ',');
    }
}
