<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();

        // Lokalisasi tanggal/waktu ke Indonesia (WIB).
        // Memengaruhi translatedFormat(), diffForHumans(), monthName, dll.
        Carbon::setLocale('id');
        Date::setLocale('id');
        setlocale(LC_TIME, 'id_ID.UTF-8', 'id_ID', 'Indonesian_Indonesia.1252');

        // Aturan kata sandi: min 8 karakter, kombinasi huruf besar & kecil,
        // serta minimal satu karakter unik (simbol). Berlaku untuk semua
        // tempat yang memakai Rules\Password::defaults() — register,
        // reset password, dan ubah password.
        Password::defaults(function () {
            return Password::min(8)
                ->mixedCase()
                ->symbols();
        });
    }
}
