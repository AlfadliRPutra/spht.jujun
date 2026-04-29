<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderShipping extends Model
{
    protected $fillable = [
        'order_id',
        'store_id',
        'store_name',
        'zone',
        'zone_label',
        'base_fee',
        'extra_fee_per_kg',
        'base_weight_kg',
        'total_weight_kg',
        'shipping_cost',
    ];

    protected function casts(): array
    {
        return [
            'base_fee'         => 'decimal:2',
            'extra_fee_per_kg' => 'decimal:2',
            'base_weight_kg'   => 'integer',
            'total_weight_kg'  => 'integer',
            'shipping_cost'    => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(User::class, 'store_id');
    }
}
