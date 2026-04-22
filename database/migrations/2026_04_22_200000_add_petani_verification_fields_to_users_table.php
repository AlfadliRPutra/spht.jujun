<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nama_usaha')->nullable()->after('alamat');
            $table->text('deskripsi_usaha')->nullable()->after('nama_usaha');
            $table->string('nik', 16)->nullable()->after('deskripsi_usaha');
            $table->string('ktp_image')->nullable()->after('nik');
            $table->timestamp('verification_submitted_at')->nullable()->after('is_verified');
            $table->text('verification_note')->nullable()->after('verification_submitted_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nama_usaha',
                'deskripsi_usaha',
                'nik',
                'ktp_image',
                'verification_submitted_at',
                'verification_note',
            ]);
        });
    }
};
