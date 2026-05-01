<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\Product;
use App\Models\SubCategory;
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

        $rootCategories = Category::with(['subCategories' => fn ($q) => $q->orderBy('sort_order')->orderBy('nama')])
            ->orderBy('sort_order')
            ->orderBy('nama')
            ->get();

        $selectedCategory = $request->filled('category')
            ? Category::where('slug', $request->input('category'))->first()
            : null;

        $selectedSub = $request->filled('sub') && $selectedCategory
            ? SubCategory::where('slug', $request->input('sub'))
                ->where('category_id', $selectedCategory->id)
                ->first()
            : null;

        $subCategories = $selectedCategory?->subCategories ?? collect();

        $produk = Product::active()
            ->with(['category', 'subCategory', 'petani'])
            ->whereHas('petani', fn ($q) => $q->where('is_verified', true))
            ->when($request->filled('q'), fn ($q) => $q->where('nama', 'like', '%'.$request->input('q').'%'))
            ->when($selectedCategory, fn ($q) => $q->where('category_id', $selectedCategory->id))
            ->when($selectedSub, fn ($q) => $q->where('sub_category_id', $selectedSub->id))
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
            'selectedSub'      => $selectedSub,
            'sort'             => $sort,
            'sortOptions'      => self::SORT_MAP,
            'heroSlides'       => $heroSlides,
        ]);
    }

    public function show(Product $produk): View
    {
        abort_unless($produk->petani?->is_verified, 404);

        $produk->load('category', 'subCategory', 'petani');

        $terkait = Product::with(['category', 'subCategory'])
            ->whereHas('petani', fn ($q) => $q->where('is_verified', true))
            ->where('id', '!=', $produk->id)
            ->where('category_id', $produk->category_id)
            ->limit(4)
            ->get();

        return view('pages.pelanggan.katalog.show', compact('produk', 'terkait'));
    }
}
