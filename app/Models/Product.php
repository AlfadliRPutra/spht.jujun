<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'nama',
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
