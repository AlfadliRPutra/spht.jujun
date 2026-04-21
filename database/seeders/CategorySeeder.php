<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Sayuran',
            'Buah-buahan',
            'Palawija',
            'Rempah & Bumbu',
            'Biji-bijian',
            'Umbi-umbian',
        ];

        foreach ($categories as $nama) {
            Category::create(['nama' => $nama]);
        }
    }
}
