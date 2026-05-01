<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            ['nama' => 'Sayuran',         'icon' => 'salad',            'children' => ['Sayur Daun', 'Sayur Buah', 'Sayur Akar']],
            ['nama' => 'Buah-buahan',     'icon' => 'apple',            'children' => ['Buah Tropis', 'Buah Lokal']],
            ['nama' => 'Palawija',        'icon' => 'plant-2',          'children' => ['Jagung & Kacang', 'Kedelai']],
            ['nama' => 'Rempah & Bumbu',  'icon' => 'pepper',           'children' => ['Rempah Segar', 'Bumbu Kering']],
            ['nama' => 'Biji-bijian',     'icon' => 'wheat',            'children' => ['Beras', 'Beras Khusus']],
            ['nama' => 'Umbi-umbian',     'icon' => 'carrot',           'children' => ['Umbi Manis', 'Umbi Pati']],
        ];

        // Idempoten: re-run seeder tidak melanggar unique('nama').
        // updateOrCreate dipakai supaya field icon/sort_order/parent_id ikut
        // tersinkron kalau definisi tree berubah.
        foreach ($tree as $sort => $parent) {
            $root = Category::updateOrCreate(
                ['nama' => $parent['nama']],
                [
                    'icon'       => $parent['icon'],
                    'sort_order' => $sort,
                    'parent_id'  => null,
                ],
            );

            foreach ($parent['children'] as $childSort => $childName) {
                Category::updateOrCreate(
                    ['nama' => $childName],
                    [
                        'parent_id'  => $root->id,
                        'sort_order' => $childSort,
                    ],
                );
            }
        }
    }
}
