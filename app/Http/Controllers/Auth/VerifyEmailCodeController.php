<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Support\EmailVerificationCode;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VerifyEmailCodeController extends Controller
{
    /**
     * Verifikasi email memakai kode 6-digit yang dikirim via email.
     * Alternatif untuk magic-link — keduanya valid bersamaan, kode pertama
     * yang berhasil akan langsung melepas status "belum diverifikasi".
     *
     * @throws ValidationException
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ], [
            'code.required' => 'Kode verifikasi wajib diisi.',
            'code.digits'   => 'Kode verifikasi harus 6 digit angka.',
        ]);

        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return $this->intended($user);
        }

        if (! EmailVerificationCode::verify($user, (string) $request->input('code'))) {
            throw ValidationException::withMessages([
                'code' => 'Kode salah atau sudah kedaluwarsa. Minta kode baru lewat tombol "Kirim ulang".',
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->intended($user)->with('status', 'email-verified');
    }

    private function intended($user): RedirectResponse
    {
        $fallback = $user->role === UserRole::Pelanggan
            ? route('pelanggan.katalog.index', absolute: false)
            : route('dashboard', absolute: false);

        return redirect()->intended($fallback.'?verified=1');
    }
}
