<?php

namespace App\Http\Controllers\Petani;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
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

    /** Maksimum gambar tambahan (di luar gambar utama). */
    private const MAX_GALLERY = 5;

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
            ->with(['category', 'subCategory', 'images'])
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
        $this->guardAgainstTruncatedUpload($request);

        $data = $this->validateProduk($request, isCreate: true);

        $data['gambar']  = PublicUpload::store($request->file('gambar'), 'products');
        $data['user_id'] = $request->user()->id;

        $extra = $request->file('gambar_extra', []);
        unset($data['gambar_extra']);

        $produk = Product::create($data);

        $this->saveGalleryImages($produk, $extra);

        return redirect()
            ->route('petani.produk.index')
            ->with('success', 'Produk berhasil ditambahkan.');
    }

    public function update(Request $request, Product $produk): RedirectResponse
    {
        abort_unless($produk->user_id === Auth::id(), 403);

        $this->guardAgainstTruncatedUpload($request);

        $data = $this->validateProduk($request, isCreate: false);

        if ($request->hasFile('gambar')) {
            PublicUpload::delete($produk->gambar);
            $data['gambar'] = PublicUpload::store($request->file('gambar'), 'products');
        } else {
            unset($data['gambar']);
        }

        // Hapus gambar tambahan yang dipilih user (checkbox).
        $deleteIds = (array) $request->input('delete_images', []);
        if (! empty($deleteIds)) {
            $toDelete = $produk->images()->whereIn('id', $deleteIds)->get();
            foreach ($toDelete as $img) {
                PublicUpload::delete($img->path);
                $img->delete();
            }
        }

        $extra = $request->file('gambar_extra', []);
        unset($data['gambar_extra']);

        $produk->update($data);

        $this->saveGalleryImages($produk, $extra);

        return redirect()
            ->route('petani.produk.index')
            ->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $produk): RedirectResponse
    {
        abort_unless($produk->user_id === Auth::id(), 403);

        PublicUpload::delete($produk->gambar);

        foreach ($produk->images as $img) {
            PublicUpload::delete($img->path);
        }

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
            'nama'             => ['required', 'string', 'max:255'],
            'category_id'      => ['required', 'exists:categories,id'],
            'sub_category_id'  => ['nullable', 'exists:sub_categories,id'],
            'harga'            => ['required', 'numeric', 'min:0'],
            'stok'             => ['required', 'integer', 'min:0'],
            'weight_kg'        => ['required', 'numeric', 'gt:0', 'max:9999.999'],
            'deskripsi'        => ['nullable', 'string', 'max:5000'],
            'gambar'           => $gambarRule,
            'gambar_extra'     => ['nullable', 'array', 'max:'.self::MAX_GALLERY],
            'gambar_extra.*'   => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'delete_images'    => ['nullable', 'array'],
            'delete_images.*'  => ['integer'],
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
            'gambar_extra.max'         => 'Maksimal :max foto tambahan.',
            'gambar_extra.*.image'     => 'Setiap foto tambahan harus berupa gambar.',
            'gambar_extra.*.mimes'     => 'Foto tambahan harus berformat JPG, PNG, atau WEBP.',
            'gambar_extra.*.max'       => 'Ukuran tiap foto tambahan maksimal 4 MB.',
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

    /**
     * Deteksi kondisi "post body terpotong" sebelum validasi berjalan. Saat
     * total ukuran upload melebihi `post_max_size` di php.ini, PHP membuang
     * seluruh body — $_POST dan $_FILES tampak kosong padahal user mengisi
     * form. Tanpa cek ini Laravel hanya akan melempar error "field wajib"
     * yang menyesatkan; di sini kita berikan pesan eksplisit + arahan teknis.
     */
    private function guardAgainstTruncatedUpload(Request $request): void
    {
        $contentLength = (int) $request->server('CONTENT_LENGTH', 0);
        if ($contentLength <= 0) {
            return;
        }

        $postMax = $this->iniSizeToBytes((string) ini_get('post_max_size'));
        if ($postMax > 0 && $contentLength > $postMax && empty($_POST) && empty($_FILES)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'gambar_extra' => 'Total ukuran upload melebihi batas server ('
                    .ini_get('post_max_size').'). Kurangi jumlah/ukuran foto, '
                    .'atau minta admin menaikkan post_max_size & upload_max_filesize di php.ini.',
            ]);
        }
    }

    private function iniSizeToBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }

        $unit   = strtolower($value[strlen($value) - 1]);
        $number = (int) $value;

        return match ($unit) {
            'g'     => $number * 1024 * 1024 * 1024,
            'm'     => $number * 1024 * 1024,
            'k'     => $number * 1024,
            default => (int) $value,
        };
    }

    /**
     * Simpan kumpulan UploadedFile sebagai galeri produk. Ditolak diam-diam
     * apabila total gambar tambahan melebihi MAX_GALLERY agar form tidak
     * gagal hanya karena hitungan terlewat satu.
     *
     * @param  array<int, \Illuminate\Http\UploadedFile|null>  $files
     */
    private function saveGalleryImages(Product $produk, array $files): void
    {
        if (empty($files)) {
            return;
        }

        $existingCount = $produk->images()->count();
        $remainingSlot = max(0, self::MAX_GALLERY - $existingCount);

        $maxOrder = (int) $produk->images()->max('sort_order');

        foreach ($files as $file) {
            if ($remainingSlot <= 0) {
                break;
            }
            if (! $file) {
                continue;
            }

            $path = PublicUpload::store($file, 'products');

            ProductImage::create([
                'product_id' => $produk->id,
                'path'       => $path,
                'sort_order' => ++$maxOrder,
            ]);

            $remainingSlot--;
        }
    }
}
