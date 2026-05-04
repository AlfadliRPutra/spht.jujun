<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    protected $fillable = [
        'zone',
        'label',
        'base_fee',
        'base_weight_kg',
        'extra_fee_per_kg',
    ];

    protected function casts(): array
    {
        return [
            'base_fee'         => 'integer',
            'base_weight_kg'   => 'integer',
            'extra_fee_per_kg' => 'integer',
        ];
    }
}
