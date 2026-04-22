<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class HeroSeeder extends Seeder
{
    public function run(): void
    {
        $slides = [
            [
                'title'      => 'Panen Segar Langsung dari Petani',
                'subtitle'   => 'Belanja hasil pertanian lokal, terjamin kualitas & harga bersahabat.',
                'image'      => 'https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=1600&auto=format&fit=crop',
                'cta_label'  => 'Belanja Sekarang',
                'cta_url'    => '/katalog',
                'is_active'  => true,
                'sort_order' => 0,
            ],
            [
                'title'      => 'Diskon 20% untuk Sayuran Segar',
                'subtitle'   => 'Nikmati potongan harga spesial minggu ini untuk semua sayur daun & buah.',
                'image'      => 'https://images.unsplash.com/photo-1518843875459-f738682238a6?w=1600&auto=format&fit=crop',
                'cta_label'  => 'Lihat Promo',
                'cta_url'    => '/katalog?sort=termurah',
                'is_active'  => true,
                'sort_order' => 1,
            ],
            [
                'title'      => 'Jadi Mitra Petani',
                'subtitle'   => 'Jual hasil panenmu di SPHT Jujun dan raih pasar yang lebih luas.',
                'image'      => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?w=1600&auto=format&fit=crop',
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
