<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memaksa petani/pelanggan melengkapi profil sebelum mengakses fitur lain.
 *
 * Definisi "lengkap" ada di User::hasCompleteProfile() — bedakan per role.
 * Admin selalu dilewatkan. Beberapa rute (profile, address, AJAX wilayah,
 * logout, verifikasi email) di-allowlist supaya user tetap bisa
 * menyelesaikan onboarding tanpa terjebak loop redirect.
 */
class EnsureProfileComplete
{
    /**
     * Nama route yang selalu boleh diakses, walau profil belum lengkap.
     */
    private const ALLOWLIST = [
        'profile.edit',
        'profile.update',
        'profile.destroy',
        'pelanggan.alamat.store',
        'pelanggan.alamat.update',
        'pelanggan.alamat.destroy',
        'pelanggan.alamat.default',
        'wilayah.cities',
        'wilayah.districts',
        'logout',
        'password.update',
        'password.confirm',
        'verification.notice',
        'verification.verify',
        'verification.send',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->isAdmin() || $user->hasCompleteProfile()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if ($routeName && in_array($routeName, self::ALLOWLIST, true)) {
            return $next($request);
        }

        return redirect()->route('profile.edit')
            ->with('error', 'Lengkapi profil Anda terlebih dahulu sebelum melanjutkan.');
    }
}
