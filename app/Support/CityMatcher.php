<?php

namespace App\Support;

/**
 * Memilih hasil pencarian destinasi Komerce yang paling cocok untuk satu
 * regency BPS. Dipakai oleh command rajaongkir:sync-cities.
 *
 * Filosofi: lebih baik mengembalikan null (tidak ter-mapping → ongkir jujur
 * "tidak tersedia") daripada salah memetakan ke kota lain (ongkir terhitung
 * untuk jarak yang keliru). Karena itu TIDAK ada fallback "ambil hasil
 * pertama" maupun "cocok provinsi saja" seperti versi lama — keduanya yang
 * dulu membuat mis. "Aceh Tenggara" nyasar ke kota Aceh lain.
 *
 * Aturan pencocokan (semua harus terpenuhi):
 *   1. Provinsi WAJIB sama — tidak pernah memetakan lintas provinsi.
 *   2. Nama dasar kota (tanpa "kabupaten/kota") harus PERSIS sama —
 *      bukan "contains", supaya "NIAS" tidak menyambar "NIAS UTARA".
 *   3. Bila ada beberapa kandidat se-nama, utamakan yang tipenya (kota vs
 *      kabupaten) sama — berguna saat Komerce mencantumkan tipe di city_name.
 */
class CityMatcher
{
    /**
     * @param  array<int, array<string,mixed>>  $hits  Hasil RajaOngkirClient::searchDestination()
     * @return array<string,mixed>|null               Kandidat terpilih, atau null bila tidak yakin.
     */
    public static function match(array $hits, string $regencyName, string $provinceName): ?array
    {
        $regBase = self::baseName($regencyName);
        $regType = self::type($regencyName);
        $regProv = self::normalizeProvince($provinceName);

        if ($regBase === '' || $regProv === '') {
            return null;
        }

        // 1. Provinsi wajib cocok.
        $inProvince = array_values(array_filter(
            $hits,
            fn ($h) => self::normalizeProvince((string) ($h['province_name'] ?? '')) === $regProv,
        ));
        if ($inProvince === []) {
            return null;
        }

        // 2. Nama dasar kota persis sama.
        $exact = array_values(array_filter(
            $inProvince,
            fn ($h) => self::baseName((string) ($h['city_name'] ?? '')) === $regBase,
        ));
        if ($exact === []) {
            return null;
        }

        // 3. Utamakan tipe (kota/kabupaten) yang sama bila Komerce mencantumkannya.
        foreach ($exact as $h) {
            if (self::type((string) ($h['city_name'] ?? '')) === $regType) {
                return $h;
            }
        }

        // Semua kandidat se-nama & se-provinsi (Komerce tak membedakan tipe):
        // ambil yang pertama — kota yang benar, perbedaan kab/kota dapat diabaikan.
        return $exact[0];
    }

    /**
     * Nama dasar kota: buang prefix administratif, rapikan huruf ter-spasi
     * (mis. "S I A K" → "SIAK"), lalu lower-case.
     */
    public static function baseName(string $name): string
    {
        $s = self::collapseSpacedLetters($name);
        $s = preg_replace('/^(kabupaten|kab\.?|kota|administrasi)\s+/iu', '', trim($s));
        $s = preg_replace('/\s+/u', ' ', (string) $s);

        return mb_strtolower(trim((string) $s));
    }

    /**
     * Tipe wilayah berdasarkan prefix nama: 'kota' bila diawali "KOTA",
     * selain itu 'kab'. (Sebagian data Komerce tidak mencantumkan tipe; dalam
     * kasus itu kandidat dianggap 'kab' dan pencocokan tipe di-skip.)
     */
    public static function type(string $name): string
    {
        return preg_match('/^\s*kota\b/iu', $name) === 1 ? 'kota' : 'kab';
    }

    private static function normalizeProvince(string $s): string
    {
        $s = preg_replace('/\s+/u', ' ', trim($s));

        return mb_strtolower((string) $s);
    }

    /**
     * Gabungkan huruf tunggal yang dipisah spasi: "D U M A I" → "DUMAI".
     * Hanya menyentuh rangkaian huruf-spasi-huruf, kata normal tidak berubah.
     */
    private static function collapseSpacedLetters(string $name): string
    {
        return (string) (preg_replace('/(?<=^|\s)(\p{L})\s(?=\p{L}\s|\p{L}$)/u', '$1', $name) ?? $name);
    }
}
