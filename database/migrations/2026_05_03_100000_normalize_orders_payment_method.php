<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sebelumnya kolom `metode_pembayaran` selalu disimpan sebagai 'midtrans'.
     * Mulai sekarang nilainya mengikuti enum App\Enums\PaymentMethod:
     *   online | cod | pickup
     *
     * Migrasi ini hanya melakukan backfill data — kolom string-nya sudah ada
     * dari migrasi awal create_orders_table.
     */
    public function up(): void
    {
        DB::table('orders')
            ->where('metode_pembayaran', 'midtrans')
            ->update(['metode_pembayaran' => 'online']);
    }

    public function down(): void
    {
        DB::table('orders')
            ->where('metode_pembayaran', 'online')
            ->update(['metode_pembayaran' => 'midtrans']);
    }
};
