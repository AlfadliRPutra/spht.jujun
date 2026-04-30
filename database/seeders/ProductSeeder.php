<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder produk demo. Dijalankan manual:
 *   php artisan db:seed --class=ProductSeeder
 *
 * Membutuhkan kategori sudah ter-seed:
 *   php artisan db:seed --class=CategorySeeder
 *
 * Idempoten: produk dicocokkan berdasarkan slug (updateOrCreate).
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $petani = $this->ensurePetani();
        $catalog = $this->catalog();

        foreach ($catalog as $kategoriSlug => $items) {
            $kategori = Category::where('slug', $kategoriSlug)->first();
            if (! $kategori) {
                $this->command?->warn("Kategori '{$kategoriSlug}' tidak ditemukan. Lewati.");
                continue;
            }

            foreach ($items as $item) {
                Product::updateOrCreate(
                    ['slug' => $item['slug']],
                    [
                        'user_id'     => $petani->id,
                        'category_id' => $kategori->id,
                        'nama'        => $item['nama'],
                        'deskripsi'   => $item['deskripsi'],
                        'harga'       => $item['harga'],
                        'stok'        => $item['stok'],
                        'weight_kg'   => $item['weight_kg'],
                        'sold_count'  => $item['sold_count'] ?? 0,
                        'is_active'   => true,
                    ],
                );
            }
        }
    }

    private function ensurePetani(): User
    {
        return User::updateOrCreate(
            ['email' => 'petani@spht.test'],
            [
                'name'            => 'Petani Demo',
                'password'        => Hash::make('password'),
                'role'            => UserRole::Petani,
                'no_hp'           => '081200000003',
                'alamat'          => 'Desa Sukamaju, Kecamatan Tani Makmur',
                'province_id'     => '12',
                'province_name'   => 'Sumatera Utara',
                'city_id'         => '1271',
                'city_name'       => 'Kota Medan',
                'district_id'     => '127101',
                'district_name'   => 'Medan Tuntungan',
                'nama_usaha'        => 'Tani Makmur Jaya',
                'deskripsi_usaha'   => 'Toko hasil tani segar langsung dari kebun.',
                'is_verified'       => true,
                'email_verified_at' => now(),
            ],
        );
    }

    /**
     * Daftar produk per slug kategori (leaf).
     * Slug kategori mengikuti nama lowercase + dash dari CategorySeeder.
     */
    private function catalog(): array
    {
        return [
            'sayur-daun' => [
                ['slug' => 'bayam-hijau-segar',   'nama' => 'Bayam Hijau Segar',   'harga' => 5000,  'stok' => 120, 'weight_kg' => 0.250, 'sold_count' => 45, 'deskripsi' => 'Bayam hijau segar dipetik pagi hari, ideal untuk sayur bening.'],
                ['slug' => 'kangkung-darat',      'nama' => 'Kangkung Darat',      'harga' => 4500,  'stok' => 150, 'weight_kg' => 0.250, 'sold_count' => 60, 'deskripsi' => 'Kangkung darat segar, batang renyah cocok untuk tumis.'],
                ['slug' => 'sawi-hijau',          'nama' => 'Sawi Hijau',          'harga' => 6000,  'stok' => 90,  'weight_kg' => 0.300, 'sold_count' => 28, 'deskripsi' => 'Sawi hijau organik, daun lebar dan tidak pahit.'],
                ['slug' => 'selada-keriting',     'nama' => 'Selada Keriting',     'harga' => 12000, 'stok' => 60,  'weight_kg' => 0.200, 'sold_count' => 18, 'deskripsi' => 'Selada keriting hidroponik, renyah untuk salad.'],
            ],
            'sayur-buah' => [
                ['slug' => 'tomat-merah',         'nama' => 'Tomat Merah',         'harga' => 12000, 'stok' => 200, 'weight_kg' => 1.000, 'sold_count' => 80, 'deskripsi' => 'Tomat merah segar, pas untuk masakan dan jus.'],
                ['slug' => 'cabai-merah-keriting','nama' => 'Cabai Merah Keriting','harga' => 35000, 'stok' => 80,  'weight_kg' => 0.500, 'sold_count' => 95, 'deskripsi' => 'Cabai merah keriting pedas tajam, langsung dari petani.'],
                ['slug' => 'mentimun-segar',      'nama' => 'Mentimun Segar',      'harga' => 8000,  'stok' => 130, 'weight_kg' => 0.500, 'sold_count' => 25, 'deskripsi' => 'Mentimun renyah dan banyak air, cocok untuk lalapan.'],
                ['slug' => 'terong-ungu',         'nama' => 'Terong Ungu',         'harga' => 9000,  'stok' => 100, 'weight_kg' => 0.500, 'sold_count' => 12, 'deskripsi' => 'Terong ungu panjang, daging empuk untuk balado.'],
            ],
            'sayur-akar' => [
                ['slug' => 'wortel-lokal',        'nama' => 'Wortel Lokal',        'harga' => 14000, 'stok' => 110, 'weight_kg' => 1.000, 'sold_count' => 42, 'deskripsi' => 'Wortel lokal manis dengan warna oranye cerah.'],
                ['slug' => 'lobak-putih',         'nama' => 'Lobak Putih',         'harga' => 11000, 'stok' => 70,  'weight_kg' => 1.000, 'sold_count' => 9,  'deskripsi' => 'Lobak putih segar untuk sup dan acar.'],
            ],
            'buah-tropis' => [
                ['slug' => 'mangga-harumanis',    'nama' => 'Mangga Harumanis',    'harga' => 28000, 'stok' => 90,  'weight_kg' => 1.000, 'sold_count' => 55, 'deskripsi' => 'Mangga Harumanis matang pohon, daging tebal dan harum.'],
                ['slug' => 'pisang-cavendish',    'nama' => 'Pisang Cavendish',    'harga' => 18000, 'stok' => 120, 'weight_kg' => 1.000, 'sold_count' => 70, 'deskripsi' => 'Pisang Cavendish kuning sempurna, manis legit.'],
                ['slug' => 'pepaya-california',   'nama' => 'Pepaya California',   'harga' => 15000, 'stok' => 75,  'weight_kg' => 1.500, 'sold_count' => 22, 'deskripsi' => 'Pepaya California ukuran sedang, daging tebal manis.'],
                ['slug' => 'nanas-madu',          'nama' => 'Nanas Madu',          'harga' => 16000, 'stok' => 60,  'weight_kg' => 1.200, 'sold_count' => 15, 'deskripsi' => 'Nanas madu manis tanpa rasa gatal.'],
            ],
            'buah-lokal' => [
                ['slug' => 'jambu-biji-merah',    'nama' => 'Jambu Biji Merah',    'harga' => 17000, 'stok' => 50,  'weight_kg' => 1.000, 'sold_count' => 11, 'deskripsi' => 'Jambu biji merah daging tebal, kaya vitamin C.'],
                ['slug' => 'salak-pondoh',        'nama' => 'Salak Pondoh',        'harga' => 22000, 'stok' => 65,  'weight_kg' => 1.000, 'sold_count' => 33, 'deskripsi' => 'Salak Pondoh manis renyah dari Sleman.'],
            ],
            'jagung-kacang' => [
                ['slug' => 'jagung-manis',        'nama' => 'Jagung Manis',        'harga' => 8000,  'stok' => 140, 'weight_kg' => 0.500, 'sold_count' => 38, 'deskripsi' => 'Jagung manis muda, biji penuh dan empuk.'],
                ['slug' => 'kacang-tanah-kupas',  'nama' => 'Kacang Tanah Kupas',  'harga' => 28000, 'stok' => 90,  'weight_kg' => 1.000, 'sold_count' => 26, 'deskripsi' => 'Kacang tanah kupas pilihan, gurih dan kering.'],
                ['slug' => 'kacang-hijau',        'nama' => 'Kacang Hijau',        'harga' => 25000, 'stok' => 80,  'weight_kg' => 1.000, 'sold_count' => 14, 'deskripsi' => 'Kacang hijau bersih siap untuk bubur dan jus.'],
            ],
            'kedelai' => [
                ['slug' => 'kedelai-lokal',       'nama' => 'Kedelai Lokal',       'harga' => 18000, 'stok' => 100, 'weight_kg' => 1.000, 'sold_count' => 17, 'deskripsi' => 'Kedelai lokal kuning, cocok untuk tempe dan tahu.'],
            ],
            'rempah-segar' => [
                ['slug' => 'jahe-merah',          'nama' => 'Jahe Merah',          'harga' => 30000, 'stok' => 70,  'weight_kg' => 0.500, 'sold_count' => 41, 'deskripsi' => 'Jahe merah segar, hangat di tubuh untuk wedang.'],
                ['slug' => 'kunyit-segar',        'nama' => 'Kunyit Segar',        'harga' => 18000, 'stok' => 85,  'weight_kg' => 0.500, 'sold_count' => 19, 'deskripsi' => 'Kunyit segar, warna oranye pekat untuk masakan.'],
                ['slug' => 'lengkuas',            'nama' => 'Lengkuas',            'harga' => 14000, 'stok' => 60,  'weight_kg' => 0.500, 'sold_count' => 8,  'deskripsi' => 'Lengkuas segar, aroma kuat untuk bumbu rendang.'],
            ],
            'bumbu-kering' => [
                ['slug' => 'bawang-putih-tunggal','nama' => 'Bawang Putih Tunggal','harga' => 55000, 'stok' => 50,  'weight_kg' => 0.500, 'sold_count' => 23, 'deskripsi' => 'Bawang putih tunggal (lanang), berkhasiat dan tahan lama.'],
                ['slug' => 'bawang-merah',        'nama' => 'Bawang Merah',        'harga' => 36000, 'stok' => 100, 'weight_kg' => 1.000, 'sold_count' => 64, 'deskripsi' => 'Bawang merah brebes ukuran sedang, aroma kuat.'],
            ],
            'beras' => [
                ['slug' => 'beras-pandan-wangi',  'nama' => 'Beras Pandan Wangi',  'harga' => 75000, 'stok' => 40,  'weight_kg' => 5.000, 'sold_count' => 30, 'deskripsi' => 'Beras Pandan Wangi 5 kg, pulen dan beraroma alami.'],
                ['slug' => 'beras-ir64',          'nama' => 'Beras IR64',          'harga' => 62000, 'stok' => 60,  'weight_kg' => 5.000, 'sold_count' => 48, 'deskripsi' => 'Beras IR64 5 kg, kualitas premium untuk konsumsi harian.'],
            ],
            'beras-khusus' => [
                ['slug' => 'beras-merah-organik', 'nama' => 'Beras Merah Organik', 'harga' => 95000, 'stok' => 30,  'weight_kg' => 5.000, 'sold_count' => 12, 'deskripsi' => 'Beras merah organik 5 kg, kaya serat untuk diet sehat.'],
                ['slug' => 'beras-hitam',         'nama' => 'Beras Hitam',         'harga' => 110000,'stok' => 25,  'weight_kg' => 2.000, 'sold_count' => 7,  'deskripsi' => 'Beras hitam 2 kg, tinggi antioksidan.'],
            ],
            'umbi-manis' => [
                ['slug' => 'ubi-cilembu',         'nama' => 'Ubi Cilembu',         'harga' => 18000, 'stok' => 80,  'weight_kg' => 1.000, 'sold_count' => 35, 'deskripsi' => 'Ubi Cilembu madu, manis legit setelah dipanggang.'],
                ['slug' => 'ubi-jalar-ungu',      'nama' => 'Ubi Jalar Ungu',      'harga' => 14000, 'stok' => 95,  'weight_kg' => 1.000, 'sold_count' => 16, 'deskripsi' => 'Ubi jalar ungu, kaya antosianin untuk kue dan kolak.'],
            ],
            'umbi-pati' => [
                ['slug' => 'singkong-segar',      'nama' => 'Singkong Segar',      'harga' => 9000,  'stok' => 150, 'weight_kg' => 2.000, 'sold_count' => 21, 'deskripsi' => 'Singkong segar 2 kg, cocok untuk gorengan dan getuk.'],
                ['slug' => 'kentang-granola',     'nama' => 'Kentang Granola',     'harga' => 16000, 'stok' => 110, 'weight_kg' => 1.000, 'sold_count' => 52, 'deskripsi' => 'Kentang Granola pilihan, kulit mulus, daging padat.'],
            ],
        ];
    }
}
