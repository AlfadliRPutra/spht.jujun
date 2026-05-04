<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShippingRate;
use App\Services\ShippingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TarifOngkirController extends Controller
{
    public function index(): View
    {
        $rates = ShippingRate::query()->get()->keyBy('zone');

        // Pastikan keempat zona selalu muncul, walau ada baris yang belum
        // di-seed (mis. database lama). Zona yang hilang ditampilkan dengan
        // nilai kosong agar admin bisa langsung mengisinya lewat form.
        $zones = array_map(function (string $zone) use ($rates) {
            return $rates->get($zone) ?? new ShippingRate([
                'zone'             => $zone,
                'label'            => self::defaultLabel($zone),
                'base_fee'         => 0,
                'base_weight_kg'   => 0,
                'extra_fee_per_kg' => 0,
            ]);
        }, ShippingService::ZONES);

        return view('pages.admin.tarif-ongkir.index', compact('zones'));
    }

    public function update(Request $request): RedirectResponse
    {
        $allowedZones = ShippingService::ZONES;

        $data = $request->validate([
            'rates'                       => ['required', 'array'],
            'rates.*.label'               => ['required', 'string', 'max:100'],
            'rates.*.base_fee'            => ['required', 'integer', 'min:0', 'max:99999999'],
            'rates.*.base_weight_kg'      => ['required', 'integer', 'min:0', 'max:1000'],
            'rates.*.extra_fee_per_kg'    => ['required', 'integer', 'min:0', 'max:99999999'],
        ], [
            'rates.*.label.required'            => 'Nama zona wajib diisi.',
            'rates.*.base_fee.required'         => 'Tarif dasar wajib diisi.',
            'rates.*.base_fee.integer'          => 'Tarif dasar harus berupa angka bulat.',
            'rates.*.base_fee.min'              => 'Tarif dasar tidak boleh negatif.',
            'rates.*.base_weight_kg.required'   => 'Berat dasar wajib diisi.',
            'rates.*.base_weight_kg.integer'    => 'Berat dasar harus berupa angka bulat.',
            'rates.*.base_weight_kg.min'        => 'Berat dasar tidak boleh negatif.',
            'rates.*.extra_fee_per_kg.required' => 'Tarif per kg ekstra wajib diisi.',
            'rates.*.extra_fee_per_kg.integer'  => 'Tarif per kg ekstra harus berupa angka bulat.',
            'rates.*.extra_fee_per_kg.min'      => 'Tarif per kg ekstra tidak boleh negatif.',
        ]);

        foreach ($data['rates'] as $zone => $row) {
            if (! in_array($zone, $allowedZones, true)) {
                continue; // abaikan zona yang tidak dikenali
            }

            ShippingRate::updateOrCreate(
                ['zone' => $zone],
                [
                    'label'            => $row['label'],
                    'base_fee'         => (int) $row['base_fee'],
                    'base_weight_kg'   => (int) $row['base_weight_kg'],
                    'extra_fee_per_kg' => (int) $row['extra_fee_per_kg'],
                ],
            );
        }

        return redirect()
            ->route('admin.tarif-ongkir.index')
            ->with('success', 'Tarif ongkir berhasil diperbarui.');
    }

    private static function defaultLabel(string $zone): string
    {
        return match ($zone) {
            ShippingService::ZONE_SAME_DISTRICT    => 'Satu Kecamatan',
            ShippingService::ZONE_SAME_CITY        => 'Satu Kabupaten/Kota',
            ShippingService::ZONE_SAME_PROVINCE    => 'Satu Provinsi',
            ShippingService::ZONE_OUTSIDE_PROVINCE => 'Luar Provinsi',
            default                                => $zone,
        };
    }
}
