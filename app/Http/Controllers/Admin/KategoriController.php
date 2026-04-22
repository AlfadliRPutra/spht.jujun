<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriController extends Controller
{
    private const SORT_MAP = [
        'sort_order' => ['sort_order', 'asc',  'Urutan'],
        'name_asc'   => ['nama',       'asc',  'Nama A-Z'],
        'name_desc'  => ['nama',       'desc', 'Nama Z-A'],
        'latest'     => ['created_at', 'desc', 'Terbaru'],
        'product_desc' => ['products_count', 'desc', 'Produk Terbanyak'],
    ];

    public function index(Request $request): View
    {
        $sortOptions = array_map(fn ($v) => $v[2], self::SORT_MAP);
        $sort = array_key_exists($request->input('sort'), self::SORT_MAP) ? $request->input('sort') : 'sort_order';
        [$sortCol, $sortDir] = self::SORT_MAP[$sort];

        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page') : 25;

        $items = Category::query()
            ->withCount('products')
            ->with('parent')
            ->when($request->filled('q'),        fn ($q) => $q->where('nama', 'like', '%'.$request->input('q').'%'))
            ->when($request->input('level') === 'root',  fn ($q) => $q->whereNull('parent_id'))
            ->when($request->input('level') === 'sub',   fn ($q) => $q->whereNotNull('parent_id'))
            ->orderBy($sortCol, $sortDir)
            ->orderBy('nama')
            ->paginate($perPage)
            ->withQueryString();

        $roots = Category::whereNull('parent_id')->orderBy('nama')->get(['id', 'nama']);

        return view('pages.admin.kategori.index', compact('items', 'sort', 'sortOptions', 'perPage', 'roots'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Category::create($data);

        return back()->with('success', 'Kategori "'.$data['nama'].'" ditambahkan.');
    }

    public function update(Request $request, Category $kategori): RedirectResponse
    {
        $data = $this->validated($request, $kategori);

        if (! empty($data['parent_id']) && (int) $data['parent_id'] === $kategori->id) {
            return back()->withErrors(['parent_id' => 'Kategori tidak bisa menjadi parent dirinya sendiri.']);
        }

        $kategori->update($data);

        return back()->with('success', 'Kategori "'.$kategori->nama.'" diperbarui.');
    }

    public function destroy(Category $kategori): RedirectResponse
    {
        if ($kategori->products()->exists()) {
            return back()->with('error', 'Kategori "'.$kategori->nama.'" tidak bisa dihapus karena masih memiliki produk.');
        }

        if ($kategori->children()->exists()) {
            return back()->with('error', 'Kategori "'.$kategori->nama.'" masih memiliki sub-kategori. Hapus atau pindahkan dulu.');
        }

        $nama = $kategori->nama;
        $kategori->delete();

        return back()->with('success', 'Kategori "'.$nama.'" dihapus.');
    }

    private function validated(Request $request, ?Category $current = null): array
    {
        return $request->validate([
            'nama'       => ['required', 'string', 'max:255'],
            'parent_id'  => ['nullable', 'integer', 'exists:categories,id'],
            'icon'       => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }
}
