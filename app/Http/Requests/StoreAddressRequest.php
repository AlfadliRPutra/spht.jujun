<?php

namespace App\Http\Requests;

use App\Support\Wilayah;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'label'           => ['nullable', 'string', 'max:50'],
            'nama_penerima'   => ['required', 'string', 'max:255'],
            'no_hp_penerima'  => ['required', 'string', 'max:30'],
            'province_id'     => ['required', 'string', 'max:32'],
            'city_id'         => ['required', 'string', 'max:32'],
            'district_id'     => ['required', 'string', 'max:32'],
            'alamat'          => ['required', 'string', 'max:1000'],
            'is_default'      => ['nullable', 'boolean'],
        ];
    }

    /**
     * Resolusi nama wilayah dari ID supaya konsisten dengan tabel master.
     * Kombinasi province/city/district yang tidak valid akan ditolak.
     */
    public function passedValidation(): void
    {
        $provinceId = $this->input('province_id');
        $cityId     = $this->input('city_id');
        $districtId = $this->input('district_id');

        if (! Wilayah::isValid($provinceId, $cityId, $districtId)) {
            abort(422, 'Kombinasi provinsi/kota/kecamatan tidak valid.');
        }

        $this->merge([
            'province_name' => Wilayah::provinceName($provinceId),
            'city_name'     => Wilayah::cityName($provinceId, $cityId),
            'district_name' => Wilayah::districtName($provinceId, $cityId, $districtId),
            'is_default'    => (bool) $this->boolean('is_default'),
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        if ($key !== null) {
            return $data;
        }
        return array_merge($data, $this->only(['province_name', 'city_name', 'district_name']));
    }
}
