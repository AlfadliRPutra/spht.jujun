<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    private const STATUS_ID = [
        Password::PASSWORD_RESET   => 'Kata sandi berhasil diatur ulang. Silakan masuk dengan kata sandi baru.',
        Password::INVALID_USER     => 'Email ini tidak terdaftar di sistem.',
        Password::INVALID_TOKEN    => 'Tautan reset sudah tidak berlaku. Silakan minta tautan baru.',
        Password::RESET_THROTTLED  => 'Reset kata sandi baru saja diminta. Tunggu beberapa saat lagi.',
    ];

    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.required'     => 'Email wajib diisi.',
            'email.email'        => 'Format email tidak valid.',
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min'       => 'Kata sandi minimal :min karakter.',
            'password.mixed'     => 'Kata sandi harus mengandung huruf besar dan huruf kecil.',
            'password.symbols'   => 'Kata sandi harus mengandung minimal satu karakter unik (mis. !@#$%).',
            'password.numbers'   => 'Kata sandi harus mengandung minimal satu angka.',
            'password.letters'   => 'Kata sandi harus mengandung minimal satu huruf.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password'       => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        $message = self::STATUS_ID[$status] ?? __($status);

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', $message);
        }

        $field = $status === Password::INVALID_TOKEN ? 'password' : 'email';

        return back()
            ->withInput($request->only('email'))
            ->withErrors([$field => $message]);
    }
}
