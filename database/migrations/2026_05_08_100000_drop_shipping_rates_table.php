<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop tabel `shipping_rates` setelah migrasi penuh ke RajaOngkir API.
     * Tarif zona tidak lagi dipakai sebagai fallback — bila API gagal,
     * checkout diblokir dengan pesan jelas. Lihat ShippingService.
     */
    public function up(): void
    {
        Schema::dropIfExists('shipping_rates');
    }

    public function down(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->string('zone', 32)->unique();
            $table->string('label');
            $table->unsignedInteger('base_fee');
            $table->unsignedSmallInteger('base_weight_kg');
            $table->unsignedInteger('extra_fee_per_kg');
            $table->timestamps();
        });
    }
};
