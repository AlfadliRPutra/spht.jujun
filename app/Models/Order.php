<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total_harga',
        'subtotal_produk',
        'shipping_total',
        'voucher_discount',
        'status',
        'metode_pembayaran',
        'snap_token',
        'midtrans_order_id',
        'payment_type',
        'payment_status',
        'nama_penerima',
        'no_hp_penerima',
        'alamat_pengiriman',
        'shipping_province_id',
        'shipping_province_name',
        'shipping_city_id',
        'shipping_city_name',
        'shipping_district_id',
        'shipping_district_name',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'total_harga'      => 'decimal:2',
            'subtotal_produk'  => 'decimal:2',
            'shipping_total'   => 'decimal:2',
            'voucher_discount' => 'decimal:2',
            'status'           => OrderStatus::class,
            'paid_at'          => 'datetime',
        ];
    }

    protected function code(): Attribute
    {
        return Attribute::get(fn () => 'INV-'
            .($this->created_at?->format('Ymd') ?? date('Ymd'))
            .'-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippings(): HasMany
    {
        return $this->hasMany(OrderShipping::class);
    }
}
