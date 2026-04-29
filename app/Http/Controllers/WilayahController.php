<?php

namespace App\Http\Controllers;

use App\Support\Wilayah;
use Illuminate\Http\JsonResponse;

/**
 * Endpoint JSON untuk cascading dropdown wilayah administratif.
 *
 * Dipakai oleh form profil (dan form lain yang butuh province/city/district)
 * supaya HTML awal tetap ringan — hanya provinsi + opsi terpilih saat ini.
 */
class WilayahController extends Controller
{
    public function cities(string $province): JsonResponse
    {
        return response()->json(Wilayah::cities($province));
    }

    public function districts(string $regency): JsonResponse
    {
        return response()->json(Wilayah::districts($regency));
    }
}
