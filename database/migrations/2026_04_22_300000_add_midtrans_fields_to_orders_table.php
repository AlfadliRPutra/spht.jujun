<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('snap_token')->nullable()->after('metode_pembayaran');
            $table->string('midtrans_order_id')->nullable()->unique()->after('snap_token');
            $table->string('payment_type')->nullable()->after('midtrans_order_id');
            $table->string('payment_status')->nullable()->after('payment_type');
            $table->text('nama_penerima')->nullable()->after('payment_status');
            $table->string('no_hp_penerima')->nullable()->after('nama_penerima');
            $table->text('alamat_pengiriman')->nullable()->after('no_hp_penerima');
            $table->timestamp('paid_at')->nullable()->after('alamat_pengiriman');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'snap_token',
                'midtrans_order_id',
                'payment_type',
                'payment_status',
                'nama_penerima',
                'no_hp_penerima',
                'alamat_pengiriman',
                'paid_at',
            ]);
        });
    }
};
