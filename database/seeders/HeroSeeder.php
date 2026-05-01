<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class HeroSeeder extends Seeder
{
    public function run(): void
    {
        // Tanpa URL eksternal — kolom image dibiarkan null sehingga
        // model jatuh ke placeholder lokal (public/img/placeholder-hero.svg).
        // Admin meng-upload banner asli lewat halaman /admin/hero.
        $slides = [
            [
                'title'      => 'Panen Segar Langsung dari Petani',
                'subtitle'   => 'Belanja hasil pertanian lokal, terjamin kualitas & harga bersahabat.',
                'image'      => null,
                'cta_label'  => 'Belanja Sekarang',
                'cta_url'    => '/katalog',
                'is_active'  => true,
                'sort_order' => 0,
            ],
            [
                'title'      => 'Diskon untuk Sayuran Segar',
                'subtitle'   => 'Nikmati potongan harga spesial minggu ini untuk semua sayur daun & buah.',
                'image'      => null,
                'cta_label'  => 'Lihat Promo',
                'cta_url'    => '/katalog?sort=termurah',
                'is_active'  => true,
                'sort_order' => 1,
            ],
            [
                'title'      => 'Jadi Mitra Petani',
                'subtitle'   => 'Jual hasil panenmu di SPHT Jujun dan raih pasar yang lebih luas.',
                'image'      => null,
                'cta_label'  => 'Daftar Petani',
                'cta_url'    => '/register',
                'is_active'  => true,
                'sort_order' => 2,
            ],
        ];

        foreach ($slides as $slide) {
            HeroSlide::create($slide);
        }
    }
}
