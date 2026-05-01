<?php

namespace App\Notifications\Auth;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends BaseVerifyEmail
{
    /**
     * Kode 6-digit di-attach oleh User::sendEmailVerificationNotification()
     * sebelum notifikasi dikirim, supaya satu email memuat dua jalur:
     *   - tombol "Verifikasi Email" (magic link / URL bertanda)
     *   - kode untuk diketik manual di halaman verifikasi
     */
    public ?string $code = null;

    protected function buildMailMessage($url): MailMessage
    {
        $ttl = (int) config('auth.verification.expire', 60);

        $msg = (new MailMessage)
            ->subject('Verifikasi Alamat Email Anda - SPHT')
            ->greeting('Halo!')
            ->line('Terima kasih telah mendaftar di SPHT (Sentra Pemasaran Hasil Tani).')
            ->line('Ada **dua cara** untuk memverifikasi email Anda — pilih salah satu:');

        $msg->line('**1. Klik tombol di bawah** untuk verifikasi otomatis:')
            ->action('Verifikasi Email', $url);

        if ($this->code) {
            $msg->line('**2. Atau masukkan kode berikut** di halaman verifikasi:')
                ->line('## '.$this->code)
                ->line('Buka halaman verifikasi, ketik kode 6-digit di atas, lalu klik "Verifikasi".');
        }

        return $msg
            ->line('Tautan dan kode di atas akan kedaluwarsa dalam '.$ttl.' menit.')
            ->line('Jika Anda tidak membuat akun, abaikan email ini.')
            ->salutation('Salam, Tim SPHT');
    }
}
