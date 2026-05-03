<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'sub_category_id',
        'nama',
        'slug',
        'deskripsi',
        'harga',
        'stok',
        'weight_kg',
        'sold_count',
        'gambar',
        'is_active',
        'deactivation_reason',
    ];

    protected function casts(): array
    {
        return [
            'harga'     => 'decimal:2',
            'stok'      => 'integer',
            'weight_kg' => 'decimal:3',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
                return asset('img/placeholder-product.svg');
            }
            return asset($this->gambar);
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

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * URL galeri lengkap untuk halaman detail: gambar utama + semua gambar
     * tambahan. Jika produk belum punya gambar sama sekali, kembalikan
     * placeholder agar tampilan tetap aman.
     *
     * @return string[]
     */
    protected function galleryUrls(): Attribute
    {
        return Attribute::get(function () {
            $urls = [];

            if ($this->gambar) {
                $urls[] = asset($this->gambar);
            }

            foreach ($this->images as $img) {
                if ($img->path) {
                    $urls[] = asset($img->path);
                }
            }

            if ($urls === []) {
                $urls[] = asset('img/placeholder-product.svg');
            }

            return $urls;
        });
    }
}
