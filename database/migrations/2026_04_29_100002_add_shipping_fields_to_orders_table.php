<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Komponen total: total_harga = subtotal_produk + shipping_total - voucher_discount
            $table->decimal('subtotal_produk',  14, 2)->default(0)->after('total_harga');
            $table->decimal('shipping_total',   14, 2)->default(0)->after('subtotal_produk');
            $table->decimal('voucher_discount', 14, 2)->default(0)->after('shipping_total');

            // Snapshot wilayah pembeli pada saat order dibuat.
            $table->string('shipping_province_id',   32)->nullable()->after('alamat_pengiriman');
            $table->string('shipping_province_name', 100)->nullable()->after('shipping_province_id');
            $table->string('shipping_city_id',       32)->nullable()->after('shipping_province_name');
            $table->string('shipping_city_name',     100)->nullable()->after('shipping_city_id');
            $table->string('shipping_district_id',   32)->nullable()->after('shipping_city_name');
            $table->string('shipping_district_name', 100)->nullable()->after('shipping_district_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal_produk', 'shipping_total', 'voucher_discount',
                'shipping_province_id',   'shipping_province_name',
                'shipping_city_id',       'shipping_city_name',
                'shipping_district_id',   'shipping_district_name',
            ]);
        });
    }
};
