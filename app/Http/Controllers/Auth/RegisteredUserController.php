<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Daftarkan user baru. Akun hanya disimpan jika email verifikasi
     * berhasil terkirim — kalau SMTP gagal, transaksi di-rollback supaya
     * user tidak terjebak dengan akun "yatim" yang tidak bisa diverifikasi.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role'     => ['required', Rule::in([UserRole::Pelanggan->value, UserRole::Petani->value])],
        ], [
            'name.required'              => 'Nama wajib diisi.',
            'email.required'             => 'Email wajib diisi.',
            'email.email'                => 'Format email tidak valid.',
            'email.unique'               => 'Email ini sudah terdaftar.',
            'password.required'          => 'Kata sandi wajib diisi.',
            'password.confirmed'         => 'Konfirmasi kata sandi tidak cocok.',
            'password.min'               => 'Kata sandi minimal :min karakter.',
            'password.mixed'             => 'Kata sandi harus mengandung huruf besar dan huruf kecil.',
            'password.symbols'           => 'Kata sandi harus mengandung minimal satu karakter unik (mis. !@#$%).',
        ]);

        try {
            $user = DB::transaction(function () use ($request) {
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                    'role'     => $request->role,
                ]);

                // Kirim verifikasi langsung — kalau SMTP throw, transaksi dibatalkan
                // dan user tidak ter-commit. Kita tidak fire Registered event supaya
                // tidak terjadi double-send oleh listener default.
                $user->sendEmailVerificationNotification();

                return $user;
            });
        } catch (Throwable $e) {
            Log::error('Registrasi gagal: tidak bisa kirim email verifikasi.', [
                'email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->only('name', 'email', 'role'))
                ->withErrors([
                    'email' => 'Gagal mengirim email verifikasi ke alamat ini. Periksa kembali alamat email Anda atau coba beberapa saat lagi.',
                ]);
        }

        Auth::login($user);

        return redirect()->route('verification.notice');
    }
}
