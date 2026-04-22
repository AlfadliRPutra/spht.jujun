<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class HeroSlide extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'cta_label',
        'cta_url',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->image) {
                return 'https://picsum.photos/seed/spht-hero-'.$this->id.'/1600/600';
            }
            if (str_starts_with($this->image, 'http')) {
                return $this->image;
            }
            return asset('storage/'.$this->image);
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
