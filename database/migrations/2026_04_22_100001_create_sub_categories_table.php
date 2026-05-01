<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('nama');
            $table->string('slug')->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['category_id', 'nama']);
            $table->index(['category_id', 'sort_order']);
        });

        // FK products.sub_category_id baru bisa dibuat di sini karena
        // migrasi products lahir lebih dulu (2026_04_21) dari sub_categories
        // (2026_04_22). Kolomnya sudah ada di products tapi tanpa constraint.
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('sub_category_id')
                ->references('id')->on('sub_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['sub_category_id']);
        });

        Schema::dropIfExists('sub_categories');
    }
};
