<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Wilayah administratif untuk perhitungan ongkos kirim simulasi.
            // Dipakai oleh pelanggan (alamat pengiriman) dan petani (alamat toko).
            // Kolom 'alamat' yang sudah ada dipakai sebagai full_address.
            $table->string('province_id', 32)->nullable()->after('alamat');
            $table->string('province_name', 100)->nullable()->after('province_id');
            $table->string('city_id', 32)->nullable()->after('province_name');
            $table->string('city_name', 100)->nullable()->after('city_id');
            $table->string('district_id', 32)->nullable()->after('city_name');
            $table->string('district_name', 100)->nullable()->after('district_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'province_id', 'province_name',
                'city_id',     'city_name',
                'district_id', 'district_name',
            ]);
        });
    }
};
