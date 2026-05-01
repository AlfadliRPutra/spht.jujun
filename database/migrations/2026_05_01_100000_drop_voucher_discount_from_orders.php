<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cleanup kolom legacy yang dipakai untuk fitur diskon di awal pengembangan.
 * Aman dijalankan walaupun kolom tidak ada (untuk DB yang sudah migrate:fresh
 * setelah migrasi sebelumnya dibersihkan).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'voucher_discount')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('voucher_discount');
            });
        }
    }

    public function down(): void
    {
        // No-op: kolom legacy tidak perlu dipulihkan.
    }
};
