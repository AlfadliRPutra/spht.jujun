<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->timestamps();

            $table->index('name');
        });

        Schema::create('regencies', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->string('province_id', 32);
            $table->timestamps();

            $table->foreign('province_id')->references('id')->on('provinces')->cascadeOnDelete();
            $table->index('name');
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->string('id', 32)->primary();
            $table->string('code', 10)->unique();
            $table->string('name', 100);
            $table->string('regency_id', 32);
            $table->timestamps();

            $table->foreign('regency_id')->references('id')->on('regencies')->cascadeOnDelete();
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('districts');
        Schema::dropIfExists('regencies');
        Schema::dropIfExists('provinces');
    }
};
