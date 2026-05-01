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
        'expires_at',
    ];

    /**
     * Window pembayaran default untuk order baru (menit).
     * Order Pending yang melewati window ini akan di-auto-cancel.
     */
    public const PAYMENT_TIMEOUT_MINUTES = 10;

    protected function casts(): array
    {
        return [
            'total_harga'      => 'decimal:2',
            'subtotal_produk'  => 'decimal:2',
            'shipping_total'   => 'decimal:2',
            'status'           => OrderStatus::class,
            'paid_at'          => 'datetime',
            'expires_at'       => 'datetime',
        ];
    }

    /**
     * True kalau order ini Pending dan window pembayaran sudah lewat.
     */
    public function isPaymentExpired(): bool
    {
        return $this->status === OrderStatus::Pending
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    /**
     * Tandai order Pending yang sudah lewat batas sebagai Batal.
     * Idempoten: kalau status sudah bukan Pending, tidak melakukan apa-apa.
     * Dipanggil lazy dari controller untuk defense-in-depth selain command terjadwal.
     */
    public function expireIfDue(): bool
    {
        if (! $this->isPaymentExpired()) {
            return false;
        }
        $this->status = OrderStatus::Batal;
        $this->save();
        return true;
    }

    /**
     * Bulk-cancel semua order Pending yang sudah melewati expires_at.
     * Dipanggil dari controller index pesanan supaya status terupdate seketika
     * (tanpa harus menunggu scheduled command jalan tiap menit).
     *
     * @return int Jumlah baris yang diubah.
     */
    public static function expireOverdue(): int
    {
        return static::query()
            ->where('status', OrderStatus::Pending)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => OrderStatus::Batal]);
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
