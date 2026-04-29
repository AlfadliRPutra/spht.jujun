<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // Dibutuhkan agar item dapat dikelompokkan per toko (= petani) tanpa
            // tergantung relasi product (yang bisa di-soft-delete).
            $table->foreignId('store_id')->nullable()->after('product_id')
                  ->constrained('users')->nullOnDelete();
            $table->decimal('weight_kg', 8, 3)->default(0)->after('jumlah');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('store_id');
            $table->dropColumn('weight_kg');
        });
    }
};
