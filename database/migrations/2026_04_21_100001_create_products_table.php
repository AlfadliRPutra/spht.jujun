<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            // FK ke sub_categories ditambahkan di migrasi 2026_04_22_100001
            // setelah tabel sub_categories ada (filename ordering: products
            // lahir lebih dulu dari sub_categories).
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->decimal('harga', 12, 2);
            $table->unsignedInteger('stok')->default(0);
            $table->string('gambar')->nullable();
            $table->timestamps();

            $table->index(['category_id', 'user_id']);
            $table->index('sub_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
