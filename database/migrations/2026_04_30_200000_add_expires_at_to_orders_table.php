<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Batas waktu pembayaran untuk order ber-status pending.
            // Order pending yang melewati expires_at di-auto-cancel oleh
            // command orders:expire-pending (dijalankan tiap menit).
            $table->timestamp('expires_at')->nullable()->after('paid_at');
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status', 'expires_at']);
            $table->dropColumn('expires_at');
        });
    }
};
