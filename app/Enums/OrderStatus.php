<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Pending  = 'pending';
    case Dibayar  = 'dibayar';
    case Dikirim  = 'dikirim';
    case Selesai  = 'selesai';
    case Batal    = 'batal';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::Dibayar => 'Dibayar',
            self::Dikirim => 'Dikirim',
            self::Selesai => 'Selesai',
            self::Batal   => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-yellow-lt',
            self::Dibayar => 'bg-blue-lt',
            self::Dikirim => 'bg-cyan-lt',
            self::Selesai => 'bg-green-lt',
            self::Batal   => 'bg-red-lt',
        };
    }
}
