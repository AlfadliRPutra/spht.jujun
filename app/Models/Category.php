<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'nama',
        'slug',
        'icon',
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
        static::saving(function (Category $category) {
            if (! empty($category->slug)) {
                return;
            }

            $base = Str::slug($category->nama) ?: 'kategori';
            $slug = $base;
            $i = 2;
            while (static::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $category->slug = $slug;
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order')->orderBy('nama');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function isLeaf(): bool
    {
        return $this->children()->doesntExist();
    }

    public function descendantIds(): array
    {
        $ids = [$this->id];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->descendantIds());
        }
        return $ids;
    }
}
