<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan ada petani, jika belum buat 3 petani
        $petanis = User::where('role', 'petani')->where('is_verified', true)->get();
        
        if ($petanis->isEmpty()) {
            $petanis = User::factory()->count(3)->petani()->create();
        }

        // Pastikan ada kategori, ambil semua kategori
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $this->call(CategorySeeder::class);
            $categories = Category::all();
        }

        // Buat produk untuk setiap petani
        foreach ($petanis as $petani) {
            foreach ($categories as $category) {
                // Tiap petani punya 2-4 produk per kategori
                Product::factory()->count(rand(2, 4))->create([
                    'user_id' => $petani->id,
                    'category_id' => $category->id,
                    'stok' => rand(10, 100),
                    'is_active' => true,
                ]);
            }
        }
    }
}
