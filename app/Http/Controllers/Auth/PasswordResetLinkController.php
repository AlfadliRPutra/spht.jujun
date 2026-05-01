<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    private const STATUS_ID = [
        Password::RESET_LINK_SENT  => 'Tautan reset kata sandi telah dikirim ke email Anda. Silakan cek inbox / folder spam.',
        Password::INVALID_USER     => 'Email ini belum terdaftar di sistem.',
        Password::RESET_THROTTLED  => 'Tautan reset baru saja dikirim. Tunggu beberapa saat sebelum meminta lagi.',
    ];

    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(
            ['email' => ['required', 'email']],
            [
                'email.required' => 'Email wajib diisi.',
                'email.email'    => 'Format email tidak valid.',
            ],
        );

        $status = Password::sendResetLink($request->only('email'));
        $message = self::STATUS_ID[$status] ?? __($status);

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', $message);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => $message]);
    }
}
