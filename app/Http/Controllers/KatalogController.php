<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KatalogController extends Controller
{
    private const SORT_MAP = [
        'latest'     => ['created_at', 'desc', 'Terbaru'],
        'terpopuler' => ['sold_count', 'desc', 'Terlaris'],
        'termurah'   => ['harga',      'asc',  'Harga Termurah'],
        'termahal'   => ['harga',      'desc', 'Harga Termahal'],
        'az'         => ['nama',       'asc',  'Nama A-Z'],
        'za'         => ['nama',       'desc', 'Nama Z-A'],
    ];

    public function index(Request $request): View
    {
        $sort = array_key_exists($request->input('sort'), self::SORT_MAP)
            ? $request->input('sort')
            : 'latest';
        [$sortCol, $sortDir] = self::SORT_MAP[$sort];

        $rootCategories = Category::roots()
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')->orderBy('nama')])
            ->orderBy('sort_order')
            ->orderBy('nama')
            ->get();

        $selectedCategory = $request->filled('category')
            ? Category::where('slug', $request->input('category'))->first()
            : null;

        $subCategories = collect();
        $activeRootSlug = null;

        if ($selectedCategory) {
            if ($selectedCategory->isRoot()) {
                $activeRootSlug = $selectedCategory->slug;
                $subCategories  = $selectedCategory->children;
            } else {
                $parent         = $selectedCategory->parent;
                $activeRootSlug = $parent?->slug;
                $subCategories  = $parent?->children ?? collect();
            }
        }

        $categoryIds = $selectedCategory?->descendantIds();

        $produk = Product::with('category', 'petani')
            ->when($request->filled('q'), fn ($q) => $q->where('nama', 'like', '%'.$request->input('q').'%'))
            ->when($categoryIds, fn ($q, $ids) => $q->whereIn('category_id', $ids))
            ->where('stok', '>', 0)
            ->orderBy($sortCol, $sortDir)
            ->orderBy('id', 'desc')
            ->get();

        $heroSlides = HeroSlide::active()->get();

        return view('pages.pelanggan.katalog.index', [
            'produk'           => $produk,
            'rootCategories'   => $rootCategories,
            'subCategories'    => $subCategories,
            'selectedCategory' => $selectedCategory,
            'activeRootSlug'   => $activeRootSlug,
            'sort'             => $sort,
            'sortOptions'      => self::SORT_MAP,
            'heroSlides'       => $heroSlides,
        ]);
    }

    public function show(Product $produk): View
    {
        $produk->load('category.parent', 'petani');

        $terkait = Product::with('category')
            ->where('id', '!=', $produk->id)
            ->where('category_id', $produk->category_id)
            ->limit(4)
            ->get();

        return view('pages.pelanggan.katalog.show', compact('produk', 'terkait'));
    }
}
