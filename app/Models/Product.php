<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'nama',
        'slug',
        'deskripsi',
        'harga',
        'stok',
        'gambar',
    ];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'stok'  => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (! empty($product->slug)) {
                return;
            }

            $base = Str::slug($product->nama) ?: 'produk';
            $slug = $base;
            $i = 2;
            while (static::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $product->slug = $slug;
        });
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->gambar) {
                return 'https://picsum.photos/seed/spht-'.$this->id.'/600/450';
            }
            if (str_starts_with($this->gambar, 'http')) {
                return $this->gambar;
            }
            return asset('storage/'.$this->gambar);
        });
    }

    public function petani(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
