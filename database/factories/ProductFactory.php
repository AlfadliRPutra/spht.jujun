<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory()->petani(),
            'category_id' => Category::factory(),
            'nama'        => fake()->words(3, true),
            'deskripsi'   => fake()->sentence(12),
            'harga'       => fake()->numberBetween(5000, 150000),
            'stok'        => fake()->numberBetween(10, 200),
            'sold_count'  => fake()->numberBetween(0, 300),
            'gambar'      => null,
            'is_active'   => true,
        ];
    }
}
