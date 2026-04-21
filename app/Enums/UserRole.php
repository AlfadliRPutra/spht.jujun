<?php

namespace App\Enums;

enum UserRole: string
{
    case Petani    = 'petani';
    case Pelanggan = 'pelanggan';
    case Admin     = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Petani    => 'Petani',
            self::Pelanggan => 'Pelanggan',
            self::Admin     => 'Admin',
        };
    }
}
