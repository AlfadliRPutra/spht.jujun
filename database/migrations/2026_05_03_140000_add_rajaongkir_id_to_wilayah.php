<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom rajaongkir_id di tabel provinces & regencies untuk
     * memetakan wilayah lokal (cuid-style) ke ID kota numerik milik
     * RajaOngkir. Diisi via artisan `php artisan rajaongkir:sync-cities`
     * (atau manual). Saat kolom kosong, ShippingService akan fallback ke
     * perhitungan berbasis zona.
     */
    public function up(): void
    {
        Schema::table('provinces', function (Blueprint $table) {
            $table->string('rajaongkir_id', 16)->nullable()->after('name');
            $table->index('rajaongkir_id');
        });

        Schema::table('regencies', function (Blueprint $table) {
            $table->string('rajaongkir_id', 16)->nullable()->after('name');
            $table->index('rajaongkir_id');
        });
    }

    public function down(): void
    {
        Schema::table('provinces', function (Blueprint $table) {
            $table->dropIndex(['rajaongkir_id']);
            $table->dropColumn('rajaongkir_id');
        });

        Schema::table('regencies', function (Blueprint $table) {
            $table->dropIndex(['rajaongkir_id']);
            $table->dropColumn('rajaongkir_id');
        });
    }
};
