<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $petani = User::where('role', UserRole::Petani)->get();
        $categoryByName = Category::pluck('id', 'nama');

        if ($petani->isEmpty() || $categoryByName->isEmpty()) {
            return;
        }

        $samples = [
            ['Bayam Hijau',        'Sayuran',          6000, 100, 'Bayam segar hasil panen pagi.',                       'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=600&auto=format&fit=crop'],
            ['Kangkung',           'Sayuran',          5000, 120, 'Kangkung darat kualitas premium.',                    'https://images.unsplash.com/photo-1605379399642-870262d3d051?w=600&auto=format&fit=crop'],
            ['Tomat',              'Sayuran',         12000,  80, 'Tomat matang pohon.',                                 'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=600&auto=format&fit=crop'],
            ['Cabai Merah',        'Sayuran',         45000,  60, 'Cabai merah pedas pilihan.',                          'https://images.unsplash.com/photo-1583119912267-cc97c911e416?w=600&auto=format&fit=crop'],
            ['Wortel',             'Sayuran',         14000,  90, 'Wortel organik manis dan renyah.',                    'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=600&auto=format&fit=crop'],
            ['Mangga Harum Manis', 'Buah-buahan',     25000,  60, 'Mangga manis dari kebun lokal.',                      'https://images.unsplash.com/photo-1605027990121-cbae9e0642df?w=600&auto=format&fit=crop'],
            ['Pisang Raja',        'Buah-buahan',     18000,  70, 'Pisang raja ukuran besar.',                           'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=600&auto=format&fit=crop'],
            ['Alpukat Mentega',    'Buah-buahan',     35000,  40, 'Alpukat lembut rasa mentega.',                        'https://images.unsplash.com/photo-1523049673857-eb18f1d7b578?w=600&auto=format&fit=crop'],
            ['Jagung Manis',       'Palawija',        10000, 150, 'Jagung manis hasil panen petani lokal.',              'https://images.unsplash.com/photo-1601593768799-76d3ee63bdf6?w=600&auto=format&fit=crop'],
            ['Kacang Tanah',       'Palawija',        22000,  90, 'Kacang tanah pilihan.',                               'https://images.unsplash.com/photo-1567529854010-c47e6c62d0ee?w=600&auto=format&fit=crop'],
            ['Kedelai',            'Palawija',        16000, 100, 'Kedelai lokal kualitas ekspor.',                      'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?w=600&auto=format&fit=crop'],
            ['Kunyit',             'Rempah & Bumbu',  15000,  50, 'Kunyit segar untuk jamu & masakan.',                  'https://images.unsplash.com/photo-1615485925600-97237c4fc1ec?w=600&auto=format&fit=crop'],
            ['Jahe Merah',         'Rempah & Bumbu',  28000,  40, 'Jahe merah asli, cocok untuk minuman herbal.',        'https://images.unsplash.com/photo-1599661046827-dacde6976549?w=600&auto=format&fit=crop'],
            ['Serai',              'Rempah & Bumbu',   8000,  70, 'Serai segar pengharum alami masakan.',                'https://images.unsplash.com/photo-1615485735879-34a9cc5a98e0?w=600&auto=format&fit=crop'],
            ['Beras Putih',        'Biji-bijian',     14000, 200, 'Beras pulen kualitas premium per kg.',                'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&auto=format&fit=crop'],
            ['Beras Merah',        'Biji-bijian',     18000, 120, 'Beras merah kaya serat untuk hidup sehat.',           'https://images.unsplash.com/photo-1536304993881-ff6e9eefa2a6?w=600&auto=format&fit=crop'],
            ['Ubi Cilembu',        'Umbi-umbian',     16000,  80, 'Ubi madu khas Cilembu.',                              'https://images.unsplash.com/photo-1596097635121-14b63b7a0c23?w=600&auto=format&fit=crop'],
            ['Singkong',           'Umbi-umbian',      7000, 110, 'Singkong lokal untuk olahan tradisional.',            'https://images.unsplash.com/photo-1598030304671-5aa1d6f21128?w=600&auto=format&fit=crop'],
            ['Kentang',            'Umbi-umbian',     13000, 130, 'Kentang granola cocok untuk segala masakan.',         'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=600&auto=format&fit=crop'],
        ];

        foreach ($samples as [$nama, $kategori, $harga, $stok, $deskripsi, $gambar]) {
            if (! $categoryByName->has($kategori)) {
                continue;
            }

            Product::create([
                'user_id'     => $petani->random()->id,
                'category_id' => $categoryByName[$kategori],
                'nama'        => $nama,
                'deskripsi'   => $deskripsi,
                'harga'       => $harga,
                'stok'        => $stok,
                'gambar'      => $gambar,
            ]);
        }
    }
}
