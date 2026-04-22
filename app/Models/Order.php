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
        'status',
        'metode_pembayaran',
    ];

    protected function casts(): array
    {
        return [
            'total_harga' => 'decimal:2',
            'status'      => OrderStatus::class,
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
}
