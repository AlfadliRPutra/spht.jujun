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
            ['Bayam Hijau',       'Sayuran',        6000,  100, 'Bayam segar hasil panen pagi.'],
            ['Kangkung',          'Sayuran',        5000,  120, 'Kangkung darat kualitas premium.'],
            ['Tomat',             'Sayuran',       12000,   80, 'Tomat matang pohon.'],
            ['Mangga Harum Manis','Buah-buahan',   25000,   60, 'Mangga manis dari kebun lokal.'],
            ['Pisang Raja',       'Buah-buahan',   18000,   70, 'Pisang raja ukuran besar.'],
            ['Jagung Manis',      'Palawija',      10000,  150, 'Jagung manis hasil panen petani lokal.'],
            ['Kacang Tanah',      'Palawija',      22000,   90, 'Kacang tanah pilihan.'],
            ['Kunyit',            'Rempah & Bumbu',15000,   50, 'Kunyit segar untuk jamu & masakan.'],
            ['Jahe Merah',        'Rempah & Bumbu',28000,   40, 'Jahe merah asli, cocok untuk minuman herbal.'],
            ['Beras Putih',       'Biji-bijian',   14000,  200, 'Beras pulen kualitas premium per kg.'],
            ['Ubi Cilembu',       'Umbi-umbian',   16000,   80, 'Ubi madu khas Cilembu.'],
            ['Singkong',          'Umbi-umbian',    7000,  110, 'Singkong lokal untuk olahan tradisional.'],
        ];

        foreach ($samples as [$nama, $kategori, $harga, $stok, $deskripsi]) {
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
                'gambar'      => null,
            ]);
        }
    }
}
