<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            ['nama' => 'Sayuran',         'icon' => 'salad',   'children' => ['Sayur Daun', 'Sayur Buah', 'Sayur Akar']],
            ['nama' => 'Buah-buahan',     'icon' => 'apple',   'children' => ['Buah Tropis', 'Buah Lokal']],
            ['nama' => 'Palawija',        'icon' => 'plant-2', 'children' => ['Jagung & Kacang', 'Kedelai']],
            ['nama' => 'Rempah & Bumbu',  'icon' => 'pepper',  'children' => ['Rempah Segar', 'Bumbu Kering']],
            ['nama' => 'Biji-bijian',     'icon' => 'wheat',   'children' => ['Beras', 'Beras Khusus']],
            ['nama' => 'Umbi-umbian',     'icon' => 'carrot',  'children' => ['Umbi Manis', 'Umbi Pati']],
        ];

        foreach ($tree as $sort => $parent) {
            $cat = Category::updateOrCreate(
                ['nama' => $parent['nama']],
                [
                    'icon'       => $parent['icon'],
                    'sort_order' => $sort,
                ],
            );

            foreach ($parent['children'] as $childSort => $childName) {
                SubCategory::updateOrCreate(
                    ['category_id' => $cat->id, 'nama' => $childName],
                    ['sort_order' => $childSort],
                );
            }
        }
    }
}
