<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'nama_penerima',
        'no_hp_penerima',
        'province_id',
        'province_name',
        'city_id',
        'city_name',
        'district_id',
        'district_name',
        'alamat',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Snapshot kompatibel dengan ShippingService::calculateShipping($buyerAddress).
     *
     * @return array{
     *     province_id:string, province_name:string,
     *     city_id:string,     city_name:string,
     *     district_id:string, district_name:string,
     *     full_address:string,
     * }
     */
    public function snapshot(): array
    {
        return [
            'province_id'   => $this->province_id,
            'province_name' => $this->province_name,
            'city_id'       => $this->city_id,
            'city_name'     => $this->city_name,
            'district_id'   => $this->district_id,
            'district_name' => $this->district_name,
            'full_address'  => $this->alamat,
        ];
    }
}
