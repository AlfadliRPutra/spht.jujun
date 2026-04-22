<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            $table->string('slug')->nullable()->after('nama');
            $table->string('icon')->nullable()->after('slug');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('icon');
            $table->index(['parent_id', 'sort_order']);
        });

        DB::table('categories')->orderBy('id')->each(function ($cat) {
            $base = Str::slug($cat->nama) ?: 'kategori';
            $slug = $base;
            $i = 2;
            while (DB::table('categories')->where('slug', $slug)->where('id', '!=', $cat->id)->exists()) {
                $slug = $base.'-'.$i++;
            }
            DB::table('categories')->where('id', $cat->id)->update(['slug' => $slug]);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('slug')->nullable(false)->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropIndex(['parent_id', 'sort_order']);
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'slug', 'icon', 'sort_order']);
        });
    }
};
