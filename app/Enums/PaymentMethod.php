<?php

namespace App\Enums;

/**
 * Metode pembayaran/pengiriman yang dipilih pelanggan saat checkout.
 *
 * - Online: ongkir terhitung normal, bayar via Midtrans (Snap).
 * - Cod   : ongkir terhitung normal, bayar tunai saat barang sampai (skip Midtrans).
 * - Pickup: ongkir 0 untuk semua toko, pelanggan ambil sendiri di toko, bayar tunai
 *           saat ambil (skip Midtrans).
 */
enum PaymentMethod: string
{
    case Online = 'online';
    case Cod    = 'cod';
    case Pickup = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::Online => 'Bayar Online',
            self::Cod    => 'Bayar di Tempat (COD)',
            self::Pickup => 'Ambil di Toko',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Online => 'Online',
            self::Cod    => 'COD',
            self::Pickup => 'Pickup',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Online => 'Bayar lewat Virtual Account, QRIS, e-wallet, atau kartu — dijaga oleh Midtrans.',
            self::Cod    => 'Pesanan diantar kurir/petani, bayar tunai saat barang sampai. Ongkir tetap berlaku.',
            self::Pickup => 'Anda ambil sendiri di toko petani. Tidak ada ongkir, bayar tunai saat ambil.',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Online => 'credit-card',
            self::Cod    => 'cash',
            self::Pickup => 'building-store',
        };
    }

    public function usesMidtrans(): bool
    {
        return $this === self::Online;
    }

    public function isPickup(): bool
    {
        return $this === self::Pickup;
    }

    /**
     * Coba parse string ke enum, fallback ke Online.
     */
    public static function fromInput(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::Online;
    }
}
