<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris per (order, store) — menyimpan rincian ongkir per toko
        // yang dihitung oleh ShippingService saat checkout/payment dibuat.
        Schema::create('order_shippings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('users')->cascadeOnDelete();
            $table->string('store_name', 255)->nullable();

            $table->string('zone', 20);              // same_district | same_city | same_province | outside_province
            $table->string('zone_label', 50);        // Satu Kecamatan / Satu Kabupaten/Kota / Satu Provinsi / Luar Provinsi
            $table->decimal('base_fee',         12, 2);
            $table->decimal('extra_fee_per_kg', 12, 2);
            $table->unsignedInteger('base_weight_kg');
            $table->unsignedInteger('total_weight_kg');
            $table->decimal('shipping_cost',    12, 2);

            $table->timestamps();

            $table->unique(['order_id', 'store_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_shippings');
    }
};
