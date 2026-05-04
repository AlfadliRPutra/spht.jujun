<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->string('zone', 32)->unique();
            $table->string('label');
            $table->unsignedInteger('base_fee');
            $table->unsignedSmallInteger('base_weight_kg');
            $table->unsignedInteger('extra_fee_per_kg');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
