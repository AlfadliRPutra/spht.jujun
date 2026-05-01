<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use App\Support\PublicUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $selectedCategory = $request->filled('category')
            ? Category::where('slug', $request->input('category'))->first()
            : null;
        $selectedSub = $request->filled('sub')
            ? SubCategory::where('slug', $request->input('sub'))->first()
            : null;

        $items = $request->user()->products()
            ->with(['category', 'subCategory'])
            ->when($request->filled('q'),              fn ($q) => $q->where('nama', 'like', '%'.$request->input('q').'%'))
            ->when($selectedCategory,                  fn ($q) => $q->where('category_id', $selectedCategory->id))
            ->when($selectedSub,                       fn ($q) => $q->where('sub_category_id', $selectedSub->id))
            ->when($request->input('stock') === 'low', fn ($q) => $q->where('stok', '<=', 20))
            ->when($request->input('stock') === 'out', fn ($q) => $q->where('stok', 0))
            ->orderBy($sortCol, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        $categories = Category::with('subCategories')->orderBy('sort_order')->orderBy('nama')->get();

        return view('pages.petani.produk.index', compact(
            'items', 'categories', 'sort', 'sortOptions', 'perPage',
            'selectedCategory', 'selectedSub'
        ));
    }

    public function create(): View
    {
        $categories = Category::with('subCategories')->orderBy('sort_order')->orderBy('nama')->get();

        return view('pages.petani.produk.form', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProduk($request, isCreate: true);

        $data['gambar']  = PublicUpload::store($request->file('gambar'), 'products');
        $data['user_id'] = $request->user()->id;

        Product::create($data);

        return redirect()
            ->route('petani.produk.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $produk): RedirectResponse
    {
        abort_unless($produk->user_id === Auth::id(), 403);

        $data = $this->validateProduk($request, isCreate: false);

        if ($request->hasFile('gambar')) {
            PublicUpload::delete($produk->gambar);
            $data['gambar'] = PublicUpload::store($request->file('gambar'), 'products');
        } else {
            unset($data['gambar']);
        }

        $produk->update($data);

        return redirect()
            ->route('petani.produk.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $produk): RedirectResponse
    {
        abort_unless($produk->user_id === Auth::id(), 403);

        PublicUpload::delete($produk->gambar);

        $produk->delete();

        return redirect()
            ->route('petani.produk.index')
            ->with('success', 'Produk berhasil dihapus.');
    }

    private function validateProduk(Request $request, bool $isCreate): array
    {
        $gambarRule = $isCreate
            ? ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096']
            : ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'];

        $data = $request->validate([
            'nama'            => ['required', 'string', 'max:255'],
            'category_id'     => ['required', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:sub_categories,id'],
            'harga'           => ['required', 'numeric', 'min:0'],
            'stok'            => ['required', 'integer', 'min:0'],
            'weight_kg'       => ['required', 'numeric', 'gt:0', 'max:9999.999'],
            'deskripsi'       => ['nullable', 'string', 'max:5000'],
            'gambar'          => $gambarRule,
        ], [
            'nama.required'            => 'Nama produk wajib diisi.',
            'nama.max'                 => 'Nama produk maksimal :max karakter.',
            'category_id.required'     => 'Kategori wajib dipilih.',
            'category_id.exists'       => 'Kategori yang dipilih tidak valid.',
            'sub_category_id.exists'   => 'Sub kategori yang dipilih tidak valid.',
            'harga.required'           => 'Harga wajib diisi.',
            'harga.numeric'            => 'Harga harus berupa angka.',
            'harga.min'                => 'Harga tidak boleh negatif.',
            'stok.required'            => 'Stok wajib diisi.',
            'stok.integer'             => 'Stok harus berupa bilangan bulat.',
            'stok.min'                 => 'Stok tidak boleh negatif.',
            'weight_kg.required'       => 'Berat per unit wajib diisi.',
            'weight_kg.gt'             => 'Berat per unit harus lebih dari 0 kg.',
            'weight_kg.max'            => 'Berat per unit terlalu besar (maks :max kg).',
            'deskripsi.max'            => 'Deskripsi maksimal :max karakter.',
            'gambar.required'          => 'Foto produk wajib diunggah.',
            'gambar.image'             => 'File yang diunggah harus berupa gambar.',
            'gambar.mimes'             => 'Foto produk harus berformat JPG, PNG, atau WEBP.',
            'gambar.max'               => 'Ukuran foto maksimal 4 MB.',
        ]);

        // Pastikan sub-kategori (jika diisi) konsisten dengan kategori induknya.
        if (! empty($data['sub_category_id'])) {
            $sub = SubCategory::find($data['sub_category_id']);
            if (! $sub || $sub->category_id !== (int) $data['category_id']) {
                $data['sub_category_id'] = null;
            }
        }

        return $data;
    }
}
