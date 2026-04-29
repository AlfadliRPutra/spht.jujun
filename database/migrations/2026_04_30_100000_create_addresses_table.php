<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buku alamat pelanggan (multi-address, maksimal 3 — dibatasi di controller).
        // Petani tetap pakai kolom wilayah di tabel users (alamat toko, single).
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label', 50)->nullable();
            $table->string('nama_penerima', 255);
            $table->string('no_hp_penerima', 30);
            $table->string('province_id', 32);
            $table->string('province_name', 100);
            $table->string('city_id', 32);
            $table->string('city_name', 100);
            $table->string('district_id', 32);
            $table->string('district_name', 100);
            $table->text('alamat');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
