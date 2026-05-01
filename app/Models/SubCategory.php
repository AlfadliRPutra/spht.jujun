<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SubCategory extends Model
{
    protected $fillable = [
        'category_id',
        'nama',
        'slug',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    protected static function booted(): void
    {
        static::saving(function (SubCategory $sub) {
            if (! empty($sub->slug)) {
                return;
            }

            $base = Str::slug($sub->nama) ?: 'sub-kategori';
            $slug = $base;
            $i = 2;
            while (static::where('slug', $slug)->where('id', '!=', $sub->id)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $sub->slug = $slug;
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
