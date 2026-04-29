<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Berat per unit produk dalam kilogram (mendukung 3 desimal: 0.001 kg).
            // Dipakai untuk perhitungan ongkos kirim per toko saat checkout.
            $table->decimal('weight_kg', 8, 3)->default(1)->after('stok');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('weight_kg');
        });
    }
};
