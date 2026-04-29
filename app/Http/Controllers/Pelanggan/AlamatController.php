<?php

namespace App\Http\Controllers\Pelanggan;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAddressRequest;
use App\Models\Address;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlamatController extends Controller
{
    public const MAX_ADDRESSES = 3;

    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->addresses()->count() >= self::MAX_ADDRESSES) {
            return back()->with('error', 'Maksimal '.self::MAX_ADDRESSES.' alamat. Hapus salah satu sebelum menambah baru.');
        }

        DB::transaction(function () use ($request, $user) {
            $data = $request->validated();
            // Alamat pertama otomatis jadi default; selain itu hormati pilihan user.
            $isDefault = $data['is_default'] ?? false;
            if ($user->addresses()->count() === 0) {
                $isDefault = true;
            }

            if ($isDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            $user->addresses()->create([
                'label'           => $data['label'] ?? null,
                'nama_penerima'   => $data['nama_penerima'],
                'no_hp_penerima'  => $data['no_hp_penerima'],
                'province_id'     => $data['province_id'],
                'province_name'   => $data['province_name'],
                'city_id'         => $data['city_id'],
                'city_name'       => $data['city_name'],
                'district_id'     => $data['district_id'],
                'district_name'   => $data['district_name'],
                'alamat'          => $data['alamat'],
                'is_default'      => $isDefault,
            ]);
        });

        return redirect()->route('profile.edit')
            ->with('status', 'address-saved')
            ->with('success', 'Alamat berhasil ditambahkan.');
    }

    public function update(StoreAddressRequest $request, Address $alamat): RedirectResponse
    {
        $this->authorizeAddress($request, $alamat);

        DB::transaction(function () use ($request, $alamat) {
            $data = $request->validated();
            $isDefault = $data['is_default'] ?? false;

            if ($isDefault && ! $alamat->is_default) {
                $alamat->user->addresses()->update(['is_default' => false]);
            }

            $alamat->update([
                'label'           => $data['label'] ?? null,
                'nama_penerima'   => $data['nama_penerima'],
                'no_hp_penerima'  => $data['no_hp_penerima'],
                'province_id'     => $data['province_id'],
                'province_name'   => $data['province_name'],
                'city_id'         => $data['city_id'],
                'city_name'       => $data['city_name'],
                'district_id'     => $data['district_id'],
                'district_name'   => $data['district_name'],
                'alamat'          => $data['alamat'],
                'is_default'      => $isDefault || $alamat->is_default,
            ]);
        });

        return redirect()->route('profile.edit')
            ->with('status', 'address-saved')
            ->with('success', 'Alamat berhasil diperbarui.');
    }

    public function destroy(Request $request, Address $alamat): RedirectResponse
    {
        $this->authorizeAddress($request, $alamat);

        DB::transaction(function () use ($alamat) {
            $wasDefault = $alamat->is_default;
            $userId     = $alamat->user_id;
            $alamat->delete();

            // Pastikan selalu ada satu default selama pelanggan masih punya alamat.
            if ($wasDefault) {
                $next = Address::where('user_id', $userId)->orderBy('id')->first();
                $next?->update(['is_default' => true]);
            }
        });

        return redirect()->route('profile.edit')
            ->with('success', 'Alamat dihapus.');
    }

    public function setDefault(Request $request, Address $alamat): RedirectResponse
    {
        $this->authorizeAddress($request, $alamat);

        DB::transaction(function () use ($alamat) {
            $alamat->user->addresses()->update(['is_default' => false]);
            $alamat->update(['is_default' => true]);
        });

        return redirect()->route('profile.edit')
            ->with('success', 'Alamat utama diperbarui.');
    }

    private function authorizeAddress(Request $request, Address $alamat): void
    {
        abort_unless($alamat->user_id === $request->user()->id, 403);
    }
}
