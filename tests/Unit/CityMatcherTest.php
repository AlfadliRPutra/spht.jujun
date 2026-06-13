<?php

namespace Tests\Unit;

use App\Support\CityMatcher;
use PHPUnit\Framework\TestCase;

class CityMatcherTest extends TestCase
{
    private function hit(string $id, string $city, string $province): array
    {
        return ['id' => $id, 'city_name' => $city, 'province_name' => $province];
    }

    public function test_picks_exact_city_within_province_not_a_sibling(): void
    {
        // Kasus lama yang rusak: "Aceh Tenggara" nyasar ke kota Aceh lain.
        $hits = [
            $this->hit('100', 'ACEH SELATAN', 'ACEH'),
            $this->hit('200', 'ACEH TENGGARA', 'ACEH'),
            $this->hit('300', 'ACEH TENGAH', 'ACEH'),
        ];

        $picked = CityMatcher::match($hits, 'KABUPATEN ACEH TENGGARA', 'ACEH');

        $this->assertNotNull($picked);
        $this->assertSame('200', $picked['id']);
    }

    public function test_returns_null_instead_of_wrong_city_when_no_exact_match(): void
    {
        // Tidak ada "aceh tenggara" di hasil → JANGAN ambil yang lain. Null.
        $hits = [
            $this->hit('100', 'ACEH SELATAN', 'ACEH'),
            $this->hit('400', 'BANDA ACEH', 'ACEH'),
        ];

        $this->assertNull(CityMatcher::match($hits, 'KABUPATEN ACEH TENGGARA', 'ACEH'));
    }

    public function test_never_matches_across_province(): void
    {
        $hits = [
            $this->hit('500', 'CIREBON', 'JAWA TENGAH'), // nama sama, provinsi beda
            $this->hit('600', 'CIREBON', 'JAWA BARAT'),
        ];

        $picked = CityMatcher::match($hits, 'KABUPATEN CIREBON', 'JAWA BARAT');

        $this->assertSame('600', $picked['id']);
    }

    public function test_distinguishes_kota_from_kabupaten_when_komerce_encodes_type(): void
    {
        $hits = [
            $this->hit('700', 'KABUPATEN CIREBON', 'JAWA BARAT'),
            $this->hit('800', 'KOTA CIREBON', 'JAWA BARAT'),
        ];

        $this->assertSame('800', CityMatcher::match($hits, 'KOTA CIREBON', 'JAWA BARAT')['id']);
        $this->assertSame('700', CityMatcher::match($hits, 'KABUPATEN CIREBON', 'JAWA BARAT')['id']);
    }

    public function test_falls_back_to_same_city_when_type_not_encoded(): void
    {
        // Komerce hanya punya satu entri "CIREBON" → kab & kota sama-sama
        // memetakan ke sana (kota yang benar; beda kab/kota diabaikan).
        $hits = [$this->hit('900', 'CIREBON', 'JAWA BARAT')];

        $this->assertSame('900', CityMatcher::match($hits, 'KOTA CIREBON', 'JAWA BARAT')['id']);
        $this->assertSame('900', CityMatcher::match($hits, 'KABUPATEN CIREBON', 'JAWA BARAT')['id']);
    }

    public function test_does_not_let_nias_match_nias_utara(): void
    {
        $hits = [
            $this->hit('11', 'NIAS UTARA', 'SUMATERA UTARA'),
            $this->hit('12', 'NIAS SELATAN', 'SUMATERA UTARA'),
        ];

        // "KABUPATEN NIAS" tidak boleh menyambar "NIAS UTARA"/"NIAS SELATAN".
        $this->assertNull(CityMatcher::match($hits, 'KABUPATEN NIAS', 'SUMATERA UTARA'));
        // Sebaliknya, yang spesifik tetap kena tepat.
        $this->assertSame('11', CityMatcher::match($hits, 'KABUPATEN NIAS UTARA', 'SUMATERA UTARA')['id']);
    }

    public function test_handles_spaced_letter_names(): void
    {
        $hits = [$this->hit('111', 'SIAK', 'RIAU')];

        $this->assertSame('111', CityMatcher::match($hits, 'KABUPATEN S I A K', 'RIAU')['id']);
    }

    public function test_returns_null_on_empty_hits(): void
    {
        $this->assertNull(CityMatcher::match([], 'KABUPATEN KERINCI', 'JAMBI'));
    }
}
