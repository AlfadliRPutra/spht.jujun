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
        $petani = User::where('role', UserRole::Petani)->where('is_verified', true)->get();
        $categoryByName = Category::pluck('id', 'nama');

        if ($petani->isEmpty() || $categoryByName->isEmpty()) {
            return;
        }

        $samples = [
            // [nama, sub-kategori (leaf), harga, stok, terjual, deskripsi, gambar]
            ['Bayam Hijau',        'Sayur Daun',       6000, 100, 140, 'Bayam segar hasil panen pagi.',                'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=600&auto=format&fit=crop'],
            ['Kangkung',           'Sayur Daun',       5000, 120,  95, 'Kangkung darat kualitas premium.',             'https://images.unsplash.com/photo-1605379399642-870262d3d051?w=600&auto=format&fit=crop'],
            ['Tomat',              'Sayur Buah',      12000,  80, 220, 'Tomat matang pohon.',                          'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=600&auto=format&fit=crop'],
            ['Cabai Merah',        'Sayur Buah',      45000,  60, 310, 'Cabai merah pedas pilihan.',                   'https://images.unsplash.com/photo-1583119912267-cc97c911e416?w=600&auto=format&fit=crop'],
            ['Wortel',             'Sayur Akar',      14000,  90,  75, 'Wortel organik manis dan renyah.',             'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=600&auto=format&fit=crop'],
            ['Mangga Harum Manis', 'Buah Tropis',     25000,  60, 180, 'Mangga manis dari kebun lokal.',               'https://images.unsplash.com/photo-1605027990121-cbae9e0642df?w=600&auto=format&fit=crop'],
            ['Pisang Raja',        'Buah Tropis',     18000,  70, 260, 'Pisang raja ukuran besar.',                    'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=600&auto=format&fit=crop'],
            ['Alpukat Mentega',    'Buah Lokal',      35000,  40,  55, 'Alpukat lembut rasa mentega.',                 'https://images.unsplash.com/photo-1523049673857-eb18f1d7b578?w=600&auto=format&fit=crop'],
            ['Jagung Manis',       'Jagung & Kacang', 10000, 150, 400, 'Jagung manis hasil panen petani lokal.',       'https://images.unsplash.com/photo-1601593768799-76d3ee63bdf6?w=600&auto=format&fit=crop'],
            ['Kacang Tanah',       'Jagung & Kacang', 22000,  90,  40, 'Kacang tanah pilihan.',                        'https://images.unsplash.com/photo-1567529854010-c47e6c62d0ee?w=600&auto=format&fit=crop'],
            ['Kedelai',            'Kedelai',         16000, 100,  22, 'Kedelai lokal kualitas ekspor.',               'https://images.unsplash.com/photo-1615485290382-441e4d049cb5?w=600&auto=format&fit=crop'],
            ['Kunyit',             'Rempah Segar',    15000,  50,  65, 'Kunyit segar untuk jamu & masakan.',           'https://images.unsplash.com/photo-1615485925600-97237c4fc1ec?w=600&auto=format&fit=crop'],
            ['Jahe Merah',         'Rempah Segar',    28000,  40,  90, 'Jahe merah asli, cocok untuk minuman herbal.', 'https://images.unsplash.com/photo-1599661046827-dacde6976549?w=600&auto=format&fit=crop'],
            ['Serai',              'Bumbu Kering',     8000,  70,  18, 'Serai segar pengharum alami masakan.',         'https://images.unsplash.com/photo-1615485735879-34a9cc5a98e0?w=600&auto=format&fit=crop'],
            ['Beras Putih',        'Beras',           14000, 200, 520, 'Beras pulen kualitas premium per kg.',         'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=600&auto=format&fit=crop'],
            ['Beras Merah',        'Beras Khusus',    18000, 120, 210, 'Beras merah kaya serat untuk hidup sehat.',    'https://images.unsplash.com/photo-1536304993881-ff6e9eefa2a6?w=600&auto=format&fit=crop'],
            ['Ubi Cilembu',        'Umbi Manis',      16000,  80, 130, 'Ubi madu khas Cilembu.',                       'https://images.unsplash.com/photo-1596097635121-14b63b7a0c23?w=600&auto=format&fit=crop'],
            ['Singkong',           'Umbi Pati',        7000, 110,  60, 'Singkong lokal untuk olahan tradisional.',     'https://images.unsplash.com/photo-1598030304671-5aa1d6f21128?w=600&auto=format&fit=crop'],
            ['Kentang',            'Umbi Pati',       13000, 130, 240, 'Kentang granola cocok untuk segala masakan.',  'https://images.unsplash.com/photo-1518977676601-b53f82aba655?w=600&auto=format&fit=crop'],
        ];

        // Produk yang akan dinonaktifkan sebagai demo moderasi admin
        $nonaktif = ['Serai'];
        $alasanNonaktif = 'Foto produk tidak sesuai. Mohon unggah ulang dengan pencahayaan cukup.';

        foreach ($samples as [$nama, $kategori, $harga, $stok, $terjual, $deskripsi, $gambar]) {
            if (! $categoryByName->has($kategori)) {
                continue;
            }

            $isActive = ! in_array($nama, $nonaktif, true);

            Product::create([
                'user_id'             => $petani->random()->id,
                'category_id'         => $categoryByName[$kategori],
                'nama'                => $nama,
                'deskripsi'           => $deskripsi,
                'harga'               => $harga,
                'stok'                => $stok,
                'sold_count'          => $terjual,
                'gambar'              => $gambar,
                'is_active'           => $isActive,
                'deactivation_reason' => $isActive ? null : $alasanNonaktif,
            ]);
        }
    }
}
