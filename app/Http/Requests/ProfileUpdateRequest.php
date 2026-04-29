<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Wilayah;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'no_hp'        => ['nullable', 'string', 'max:30'],
            'province_id'  => ['nullable', 'string', 'max:32'],
            'city_id'      => ['nullable', 'string', 'max:32', 'required_with:district_id'],
            'district_id'  => ['nullable', 'string', 'max:32'],
            'alamat'       => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Lengkapi nama wilayah dari ID (single source of truth: config/wilayah.php),
     * sehingga UI tidak bisa memalsukan pasangan ID/Name yang tidak konsisten.
     */
    public function passedValidation(): void
    {
        $provinceId = $this->input('province_id') ?: null;
        $cityId     = $this->input('city_id') ?: null;
        $districtId = $this->input('district_id') ?: null;

        if ($districtId && ! Wilayah::isValid($provinceId, $cityId, $districtId)) {
            // Jika kombinasi tidak valid, abaikan ID supaya tidak ada data hantu.
            $provinceId = $cityId = $districtId = null;
        }

        $this->merge([
            'province_id'   => $provinceId,
            'province_name' => Wilayah::provinceName($provinceId),
            'city_id'       => $cityId,
            'city_name'     => Wilayah::cityName($provinceId, $cityId),
            'district_id'   => $districtId,
            'district_name' => Wilayah::districtName($provinceId, $cityId, $districtId),
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);
        if ($key !== null) {
            return $data;
        }
        return array_merge($data, $this->only([
            'province_name', 'city_name', 'district_name',
        ]));
    }
}
