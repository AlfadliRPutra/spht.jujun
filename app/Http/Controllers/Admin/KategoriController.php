<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class KategoriController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100], true)
            ? (int) $request->input('per_page') : 25;

        $tab = $request->input('tab') === 'sub' ? 'sub' : 'main';

        // Tab utama: kategori induk + jumlah produk + jumlah sub
        $kategoriItems = Category::query()
            ->withCount(['products', 'subCategories'])
            ->when($tab === 'main' && $request->filled('q'),
                fn ($q) => $q->where('nama', 'like', '%'.$request->input('q').'%'))
            ->orderBy('sort_order')
            ->orderBy('nama')
            ->paginate($perPage, ['*'], 'kpage')
            ->withQueryString();

        // Tab sub: sub kategori dengan filter parent
        $parentFilter = $request->input('parent');
        $subItems = SubCategory::query()
            ->with('category')
            ->withCount('products')
            ->when($tab === 'sub' && $request->filled('q'),
                fn ($q) => $q->where('nama', 'like', '%'.$request->input('q').'%'))
            ->when($parentFilter,
                fn ($q) => $q->whereHas('category', fn ($qq) => $qq->where('slug', $parentFilter)))
            ->orderBy('category_id')
            ->orderBy('sort_order')
            ->orderBy('nama')
            ->paginate($perPage, ['*'], 'spage')
            ->withQueryString();

        $allParents = Category::orderBy('sort_order')->orderBy('nama')->get(['id', 'nama', 'slug']);

        return view('pages.admin.kategori.index', compact(
            'kategoriItems', 'subItems', 'allParents', 'perPage', 'tab'
        ));
    }

    // ── Kategori (induk) ────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama'       => ['required', 'string', 'max:255', 'unique:categories,nama'],
            'icon'       => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        Category::create($data);

        return back()->with('success', 'Kategori "'.$data['nama'].'" ditambahkan.');
    }

    public function update(Request $request, Category $kategori): RedirectResponse
    {
        $data = $request->validate([
            'nama'       => ['required', 'string', 'max:255', 'unique:categories,nama,'.$kategori->id],
            'icon'       => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $kategori->update($data);

        return back()->with('success', 'Kategori "'.$kategori->nama.'" diperbarui.');
    }

    public function destroy(Category $kategori): RedirectResponse
    {
        if ($kategori->products()->exists()) {
            return back()->with('error', 'Kategori "'.$kategori->nama.'" tidak bisa dihapus karena masih memiliki produk.');
        }

        if ($kategori->subCategories()->exists()) {
            return back()->with('error', 'Kategori "'.$kategori->nama.'" masih memiliki sub kategori. Hapus sub-kategorinya dulu.');
        }

        $nama = $kategori->nama;
        $kategori->delete();

        return back()->with('success', 'Kategori "'.$nama.'" dihapus.');
    }

    // ── Sub Kategori ────────────────────────────────────────────────────────

    public function storeSub(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'nama'        => ['required', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $exists = SubCategory::where('category_id', $data['category_id'])
            ->where('nama', $data['nama'])->exists();
        if ($exists) {
            return back()->withErrors(['nama' => 'Sub kategori dengan nama itu sudah ada di kategori ini.'])->withInput();
        }

        SubCategory::create($data);

        return redirect()->route('admin.kategori.index', ['tab' => 'sub'])
            ->with('success', 'Sub kategori "'.$data['nama'].'" ditambahkan.');
    }

    public function updateSub(Request $request, SubCategory $sub): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'nama'        => ['required', 'string', 'max:255'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
        ]);

        $exists = SubCategory::where('category_id', $data['category_id'])
            ->where('nama', $data['nama'])
            ->where('id', '!=', $sub->id)
            ->exists();
        if ($exists) {
            return back()->withErrors(['nama' => 'Sub kategori dengan nama itu sudah ada di kategori ini.'])->withInput();
        }

        $sub->update($data);

        return redirect()->route('admin.kategori.index', ['tab' => 'sub'])
            ->with('success', 'Sub kategori "'.$sub->nama.'" diperbarui.');
    }

    public function destroySub(SubCategory $sub): RedirectResponse
    {
        if ($sub->products()->exists()) {
            return back()->with('error', 'Sub kategori "'.$sub->nama.'" tidak bisa dihapus karena masih dipakai produk.');
        }

        $nama = $sub->nama;
        $sub->delete();

        return redirect()->route('admin.kategori.index', ['tab' => 'sub'])
            ->with('success', 'Sub kategori "'.$nama.'" dihapus.');
    }
}
