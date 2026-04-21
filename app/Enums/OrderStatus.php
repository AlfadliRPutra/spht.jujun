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
            self::Pending => 'bg-yellow',
            self::Dibayar => 'bg-blue',
            self::Dikirim => 'bg-cyan',
            self::Selesai => 'bg-green',
            self::Batal   => 'bg-red',
        };
    }
}
