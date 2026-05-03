@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $items */
    /** @var \Illuminate\Support\Collection<int, \App\Models\Category> $categories */
    /** @var \App\Models\Category|null $selectedCategory */
    /** @var \App\Models\SubCategory|null $selectedSub */
    $title  = 'Produk Saya';
    $active = 'petani.produk';
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Daftar Produk ({{ $items->total() }})</h3>
            <a href="{{ route('petani.produk.create') }}" class="btn btn-primary"><i class="ti ti-plus me-1"></i> Tambah Produk</a>
        </div>

        <x-table-toolbar
            :action="route('petani.produk.index')"
            placeholder="Cari produk..."
            :sort-options="$sortOptions"
            :sort="$sort"
            :per-page="$perPage">
            <x-slot name="filters">
                <div>
                    <label class="form-label small text-secondary mb-1">Kategori</label>
                    <select name="category" class="form-select js-cat-root" data-target=".js-cat-sub" style="min-width:170px">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->slug }}" @selected(request('category') === $c->slug)>{{ $c->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label small text-secondary mb-1">Sub Kategori</label>
                    <select name="sub" class="form-select js-cat-sub" data-old="{{ request('sub') }}" style="min-width:170px">
                        <option value="">Semua</option>
                    </select>
                </div>
                <div>
                    <label class="form-label small text-secondary mb-1">Stok</label>
                    <select name="stock" class="form-select" style="min-width:130px">
                        <option value="">Semua</option>
                        <option value="low" @selected(request('stock') === 'low')>Stok Rendah (≤20)</option>
                        <option value="out" @selected(request('stock') === 'out')>Habis</option>
                    </select>
                </div>
            </x-slot>
        </x-table-toolbar>

        <div class="table-responsive">
            <table class="table table-vcenter card-table">
                <thead>
                    <tr>
                        <th class="w-1"></th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th class="text-end">Harga</th>
                        <th class="text-end">Stok</th>
                        <th class="text-end">Terjual</th>
                        <th class="w-1"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $p)
                        <tr>
                            <td><img src="{{ $p->image_url }}" alt="{{ $p->nama }}" class="rounded" style="width:48px;height:48px;object-fit:cover" loading="lazy" decoding="async"></td>
                            <td>{{ $p->nama }}</td>
                            <td>
                                <span class="badge bg-blue-lt">{{ $p->category?->nama ?? '—' }}</span>
                                @if ($p->subCategory)
                                    <i class="ti ti-chevron-right text-secondary mx-1 small"></i>
                                    <span class="badge bg-green-lt">{{ $p->subCategory->nama }}</span>
                                @endif
                            </td>
                            <td class="text-end">Rp {{ number_format($p->harga, 0, ',', '.') }}</td>
                            <td class="text-end">
                                @if ($p->stok <= 0)
                                    <span class="badge bg-red">Habis</span>
                                @elseif ($p->stok <= 20)
                                    <span class="badge bg-yellow">{{ $p->stok }}</span>
                                @else
                                    {{ $p->stok }}
                                @endif
                            </td>
                            <td class="text-end">{{ $p->sold_count }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#edit-produk-{{ $p->id }}">
                                    <i class="ti ti-edit me-1"></i> Ubah
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="text-secondary small">
                Menampilkan <strong>{{ $items->firstItem() ?? 0 }}</strong> - <strong>{{ $items->lastItem() ?? 0 }}</strong>
                dari <strong>{{ $items->total() }}</strong>
            </div>
            {{ $items->links() }}
        </div>
    </div>

    @foreach ($items as $p)
        <div class="modal modal-blur fade" id="edit-produk-{{ $p->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <form method="POST" action="{{ route('petani.produk.update', $p->slug) }}" enctype="multipart/form-data" class="modal-content">
                    @csrf @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Ubah Produk</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4 text-center">
                                <img src="{{ $p->image_url }}" alt="{{ $p->nama }}"
                                     class="rounded border"
                                     style="width:100%;max-width:180px;aspect-ratio:1/1;object-fit:cover;background:#f6f8fa"
                                     loading="lazy">
                                <label class="form-label small text-secondary mt-2 mb-1 d-block">Ganti Gambar</label>
                                <input type="file" name="gambar" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="form-control form-control-sm">
                                <div class="form-text">JPG / PNG / WEBP, maks 4&nbsp;MB</div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label required">Nama Produk</label>
                                    <input type="text" name="nama" class="form-control"
                                           value="{{ old('nama', $p->nama) }}" required>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label required">Kategori</label>
                                        <select name="category_id" class="form-select js-edit-cat" required
                                                data-target="#edit-sub-{{ $p->id }}">
                                            @foreach ($categories as $c)
                                                <option value="{{ $c->id }}" @selected($p->category_id === $c->id)>{{ $c->nama }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Sub Kategori</label>
                                        <select name="sub_category_id" id="edit-sub-{{ $p->id }}" class="form-select"
                                                data-old="{{ $p->sub_category_id }}">
                                            <option value="">— Tanpa sub kategori —</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label required">Harga (Rp)</label>
                                        <input type="text" inputmode="numeric" name="harga" class="form-control js-rupiah"
                                               value="{{ number_format((int) old('harga', $p->harga), 0, ',', '.') }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label required">Stok</label>
                                        <input type="number" name="stok" min="0" class="form-control"
                                               value="{{ old('stok', $p->stok) }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label required">Berat per Unit (kg)</label>
                                        <input type="number" step="0.001" min="0.001" name="weight_kg" class="form-control"
                                               value="{{ old('weight_kg', $p->weight_kg) }}" required>
                                        <div class="form-text">Dipakai untuk perhitungan ongkos kirim.</div>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" rows="3" class="form-control">{{ old('deskripsi', $p->deskripsi) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div>
                            <label class="form-label d-flex align-items-center justify-content-between">
                                <span>Foto Tambahan</span>
                                <span class="text-secondary small">{{ $p->images->count() }}/5</span>
                            </label>

                            @if ($p->images->isNotEmpty())
                                <div class="row g-2 mb-2">
                                    @foreach ($p->images as $img)
                                        <div class="col-4 col-md-3">
                                            <label class="d-block position-relative" style="cursor:pointer">
                                                <img src="{{ $img->url }}" alt="" class="rounded border w-100"
                                                     style="aspect-ratio:1/1;object-fit:cover;background:#f6f8fa" loading="lazy">
                                                <span class="position-absolute top-0 end-0 m-1 d-flex align-items-center gap-1 bg-white bg-opacity-90 rounded px-2 py-1 small text-danger">
                                                    <input type="checkbox" name="delete_images[]" value="{{ $img->id }}"
                                                           class="form-check-input m-0 js-delete-img">
                                                    <i class="ti ti-trash"></i>
                                                </span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="form-text mb-2">Centang gambar yang ingin dihapus saat menyimpan.</div>
                            @else
                                <div class="text-secondary small mb-2"><i class="ti ti-photo-off me-1"></i>Belum ada foto tambahan.</div>
                            @endif

                            <input type="file" name="gambar_extra[]" multiple
                                   class="form-control form-control-sm js-extra-input"
                                   data-max="{{ max(0, 5 - $p->images->count()) }}"
                                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <div class="form-text">
                                Tambah foto galeri (sisa {{ max(0, 5 - $p->images->count()) }} slot). JPG/PNG/WEBP, maks 4&nbsp;MB per file.
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-2 js-extra-preview"></div>
                        </div>

                        @if (! $p->is_active && $p->deactivation_reason)
                            <div class="alert alert-warning mt-3 mb-0">
                                <div class="fw-semibold small"><i class="ti ti-alert-triangle me-1"></i> Produk dinonaktifkan admin</div>
                                <div class="small">{{ $p->deactivation_reason }}</div>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-danger"
                                data-bs-toggle="modal" data-bs-target="#hapus-produk-{{ $p->id }}"
                                data-bs-dismiss="modal">
                            <i class="ti ti-trash me-1"></i> Hapus
                        </button>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-link" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal modal-blur fade" id="hapus-produk-{{ $p->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <form method="POST" action="{{ route('petani.produk.destroy', $p->slug) }}" class="modal-content">
                    @csrf @method('DELETE')
                    <div class="modal-status bg-danger"></div>
                    <div class="modal-body text-center py-4">
                        <i class="ti ti-alert-circle text-danger mb-2" style="font-size:2.5rem"></i>
                        <h3>Hapus produk ini?</h3>
                        <div class="text-secondary mb-0">
                            <strong>{{ $p->nama }}</strong> akan dihapus dari katalog.
                            Riwayat transaksi yang sudah terjadi tetap tersimpan.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="w-100">
                            <div class="row">
                                <div class="col"><button type="button" class="btn w-100" data-bs-dismiss="modal">Batal</button></div>
                                <div class="col"><button type="submit" class="btn btn-danger w-100">Ya, Hapus</button></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    @push('scripts')
        @include('partials.extra-image-accumulator')
        <script>
            const SUB_MAP = @json(
                $categories->mapWithKeys(fn ($c) => [
                    $c->id => $c->subCategories->map(fn ($s) => ['id' => $s->id, 'slug' => $s->slug, 'nama' => $s->nama])->values()
                ])
            );
            const SUB_MAP_BY_SLUG = @json(
                $categories->mapWithKeys(fn ($c) => [
                    $c->slug => $c->subCategories->map(fn ($s) => ['slug' => $s->slug, 'nama' => $s->nama])->values()
                ])
            );

            // Cascading filter (toolbar) — pakai slug
            document.querySelectorAll('.js-cat-root').forEach(root => {
                const sub = document.querySelector(root.dataset.target);
                if (!sub) return;

                const refresh = (preselect = null) => {
                    const list = SUB_MAP_BY_SLUG[root.value] || [];
                    sub.innerHTML = '<option value="">Semua</option>';
                    list.forEach(s => {
                        const sel = s.slug === preselect ? ' selected' : '';
                        sub.insertAdjacentHTML('beforeend', `<option value="${s.slug}"${sel}>${s.nama}</option>`);
                    });
                    sub.disabled = list.length === 0;
                };
                root.addEventListener('change', () => refresh());
                refresh(sub.dataset.old);
            });

            // Cascading di setiap modal edit — pakai id
            document.querySelectorAll('.js-edit-cat').forEach(root => {
                const sub = document.querySelector(root.dataset.target);
                if (!sub) return;

                const refresh = (preselect = null) => {
                    const list = SUB_MAP[root.value] || [];
                    sub.innerHTML = '<option value="">— Tanpa sub kategori —</option>';
                    list.forEach(s => {
                        const sel = String(s.id) === String(preselect) ? ' selected' : '';
                        sub.insertAdjacentHTML('beforeend', `<option value="${s.id}"${sel}>${s.nama}</option>`);
                    });
                };
                root.addEventListener('change', () => refresh());
                refresh(sub.dataset.old);
            });

            // Dim image preview saat checkbox "hapus" dicentang
            document.querySelectorAll('.js-delete-img').forEach(cb => {
                const img = cb.closest('label')?.querySelector('img');
                const sync = () => { if (img) img.style.opacity = cb.checked ? '0.35' : '1'; };
                cb.addEventListener('change', sync);
                sync();
            });

            // Foto tambahan di tiap modal edit: pakai akumulator yang sama
            // dengan halaman create. data-max menyimpan sisa slot per produk.
            document.querySelectorAll('.js-extra-input').forEach(input => {
                const preview  = input.parentElement.querySelector('.js-extra-preview');
                const maxFiles = Math.max(0, parseInt(input.dataset.max || '0', 10));
                if (! preview || maxFiles === 0) {
                    if (preview && maxFiles === 0) {
                        preview.innerHTML = '<div class="small text-secondary"><i class="ti ti-info-circle me-1"></i>Slot penuh — hapus sebagian foto lama dulu.</div>';
                        input.disabled = true;
                    }
                    return;
                }
                window.spht_setupExtraImages?.(input, { previewEl: preview, maxFiles });
            });

            document.querySelectorAll('.js-rupiah').forEach(el => {
                const fmt = v => {
                    const n = String(v).replace(/\D/g, '');
                    return n ? Number(n).toLocaleString('id-ID') : '';
                };
                el.value = fmt(el.value);
                el.addEventListener('input', () => {
                    const before = el.value.length;
                    const caret  = el.selectionStart;
                    el.value = fmt(el.value);
                    const diff = el.value.length - before;
                    el.setSelectionRange(caret + diff, caret + diff);
                });
                el.form?.addEventListener('submit', () => {
                    el.value = el.value.replace(/\./g, '');
                });
            });
        </script>
    @endpush
</x-layouts.app>
