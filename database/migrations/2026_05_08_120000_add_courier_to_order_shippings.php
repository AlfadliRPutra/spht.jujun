<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom kurir dan service_code di order_shippings supaya
     * setiap order menyimpan kurir/service yang dipilih pelanggan
     * (mis. JNE REG, POS Paket Kilat, dll). Sebelumnya hanya `zone_label`
     * yang dipakai sebagai display string.
     */
    public function up(): void
    {
        Schema::table('order_shippings', function (Blueprint $table) {
            $table->string('courier', 32)->nullable()->after('zone_label');
            $table->string('service_code', 32)->nullable()->after('courier');
        });
    }

    public function down(): void
    {
        Schema::table('order_shippings', function (Blueprint $table) {
            $table->dropColumn(['courier', 'service_code']);
        });
    }
};
