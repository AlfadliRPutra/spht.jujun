<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProdukController extends Controller
{
    private const SORT_MAP = [
        'latest'       => ['created_at', 'desc', 'Terbaru'],
        'oldest'       => ['created_at', 'asc',  'Terlama'],
        'name_asc'     => ['nama',       'asc',  'Nama A-Z'],
        'name_desc'    => ['nama',       'desc', 'Nama Z-A'],
        'price_asc'    => ['harga',      'asc',  'Harga Terendah'],
        'price_desc'   => ['harga',      'desc', 'Harga Tertinggi'],
        'stock_asc'    => ['stok',       'asc',  'Stok Sedikit'],
        'sold_desc'    => ['sold_count', 'desc', 'Paling Laris'],
    ];

    public function index(Request $request): View
    {
        $sortOptions = array_map(fn ($v) => $v[2], self::SORT_MAP);
        $sort = array_key_exists($request->input('sort'), self::SORT_MAP) ? $request->input('sort') : 'latest';
        [$sortCol, $sortDir] = self::SORT_MAP[$sort];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page') : 10;

        $categoryIds = null;
        if ($request->filled('category')) {
            $cat = Category::where('slug', $request->input('category'))->first();
            $categoryIds = $cat?->descendantIds();
        }

        $items = $request->user()->products()
            ->with('category')
            ->when($request->filled('q'),              fn ($q) => $q->where('nama', 'like', '%'.$request->input('q').'%'))
            ->when($categoryIds,                       fn ($q, $ids) => $q->whereIn('category_id', $ids))
            ->when($request->input('stock') === 'low', fn ($q) => $q->where('stok', '<=', 20))
            ->when($request->input('stock') === 'out', fn ($q) => $q->where('stok', 0))
            ->orderBy($sortCol, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        $categories = Category::with('children')->whereNull('parent_id')->orderBy('nama')->get();

        return view('pages.petani.produk.index', compact('items', 'categories', 'sort', 'sortOptions', 'perPage'));
    }

    public function create(): View
    {
        return view('pages.petani.produk.form');
    }
}
