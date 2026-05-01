<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

/**
 * Pengelolaan kode verifikasi email 6-digit.
 *
 * Disimpan di cache (bukan kolom DB) supaya:
 *   - tidak butuh migrasi tambahan,
 *   - otomatis kedaluwarsa,
 *   - tidak bocor ke kolom yang ter-snapshot di mana-mana.
 *
 * Kode disimpan dalam bentuk hash; comparison pakai Hash::check supaya
 * kalau cache (file/redis) ke-leak isinya, kode mentah tidak terlihat.
 */
class EmailVerificationCode
{
    /** TTL kode dalam menit. Disamakan dengan magic link (auth.verification.expire). */
    public static function ttlMinutes(): int
    {
        return (int) config('auth.verification.expire', 60);
    }

    /** Generate kode baru, simpan hash-nya, kembalikan kode mentah ke caller. */
    public static function issue(User $user): string
    {
        $code = (string) random_int(100000, 999999);

        Cache::put(
            self::cacheKey($user),
            Hash::make($code),
            now()->addMinutes(self::ttlMinutes()),
        );

        return $code;
    }

    /** Cek kode yang dimasukkan user. Kode sekali pakai — habis sukses langsung dibuang. */
    public static function verify(User $user, string $code): bool
    {
        $hash = Cache::get(self::cacheKey($user));
        if (! $hash || ! Hash::check($code, $hash)) {
            return false;
        }

        Cache::forget(self::cacheKey($user));
        return true;
    }

    public static function forget(User $user): void
    {
        Cache::forget(self::cacheKey($user));
    }

    private static function cacheKey(User $user): string
    {
        return 'verify-email-code:'.$user->id;
    }
}
