<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cancel pesanan Pending yang melewati 10 menit window pembayaran.
// Pasangkan `php artisan schedule:run` di cron (* * * * *) supaya berjalan tiap menit.
Schedule::command('orders:expire-pending')->everyMinute()->withoutOverlapping();
