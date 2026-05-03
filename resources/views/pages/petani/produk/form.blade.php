@php
    /** @var \Illuminate\Support\Collection<int, \App\Models\Category> $categories */
    $title  = 'Tambah Produk';
    $active = 'petani.produk';
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="card">
        <div class="card-body">
            <x-form-errors title="Produk gagal disimpan" />

            <form action="{{ route('petani.produk.store') }}" method="POST" enctype="multipart/form-data" class="row g-3" id="produk-form" novalidate>
                @csrf
                <div class="col-md-12">
                    <label class="form-label required">Nama Produk</label>
                    <input type="text" name="nama" value="{{ old('nama') }}" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label required">Kategori</label>
                    <select name="category_id" class="form-select js-cat-root" required data-target=".js-cat-sub">
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sub Kategori</label>
                    <select name="sub_category_id" class="form-select js-cat-sub" data-old="{{ old('sub_category_id') }}">
                        <option value="">— Pilih kategori dahulu —</option>
                    </select>
                    <div class="form-text">Opsional. Akan terbuka setelah kategori dipilih.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label required">Harga</label>
                    <div class="input-group">
                        <span class="input-group-text">Rp</span>
                        <input type="text" inputmode="numeric" name="harga"
                               value="{{ old('harga') !== null && old('harga') !== '' ? number_format((int) old('harga'), 0, ',', '.') : '' }}"
                               class="form-control js-rupiah" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Stok</label>
                    <input type="number" name="stok" value="{{ old('stok') }}" class="form-control" min="0" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label required">Berat per Unit</label>
                    <div class="input-group">
                        <input type="number" name="weight_kg" step="0.001" min="0.001"
                               value="{{ old('weight_kg', '1') }}" class="form-control" required>
                        <span class="input-group-text">kg</span>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" rows="4" class="form-control">{{ old('deskripsi') }}</textarea>
                </div>

                <div class="col-12">
                    <label class="form-label required">Foto Utama</label>
                    <div class="row g-3 align-items-start">
                        <div class="col-md-4">
                            <div id="gambar-preview" class="rounded border d-flex align-items-center justify-content-center text-secondary"
                                 style="width:100%;aspect-ratio:1/1;background:#f6f8fa;overflow:hidden">
                                <div class="text-center small px-2">
                                    <i class="ti ti-photo" style="font-size:2rem"></i>
                                    <div>Belum ada gambar</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <input type="file" name="gambar" id="gambar-input" required
                                   class="form-control @error('gambar') is-invalid @enderror"
                                   accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                            <div class="form-text">
                                Format <strong>JPG / PNG / WEBP</strong>, maksimal <strong>4 MB</strong>.
                                Foto utama tampil sebagai thumbnail di katalog dan halaman detail.
                            </div>
                            @error('gambar') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            <div id="gambar-info" class="small text-secondary mt-1"></div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label">Foto Tambahan (Opsional)</label>
                    <input type="file" name="gambar_extra[]" id="gambar-extra-input" multiple
                           class="form-control @error('gambar_extra') is-invalid @enderror @error('gambar_extra.*') is-invalid @enderror"
                           accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <div class="form-text">
                        Bisa pilih beberapa file sekaligus. Maksimal <strong>5 foto</strong>, masing-masing maks 4&nbsp;MB.
                        Foto-foto ini ditampilkan sebagai galeri di halaman detail produk.
                    </div>
                    @error('gambar_extra') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    @error('gambar_extra.*') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    <div id="gambar-extra-preview" class="d-flex flex-wrap gap-2 mt-2"></div>
                </div>

                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ route('petani.produk.index') }}" class="btn btn-link">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        @include('partials.extra-image-accumulator')
        <script>
            // Cascading kategori → sub kategori
            const SUB_MAP = @json(
                $categories->mapWithKeys(fn ($c) => [
                    $c->id => $c->subCategories->map(fn ($s) => ['id' => $s->id, 'nama' => $s->nama])->values()
                ])
            );

            document.querySelectorAll('.js-cat-root').forEach(root => {
                const sub = document.querySelector(root.dataset.target);
                if (!sub) return;

                const refresh = (preselect = null) => {
                    const list = SUB_MAP[root.value] || [];
                    sub.innerHTML = '';
                    if (!root.value) {
                        sub.insertAdjacentHTML('beforeend', '<option value="">— Pilih kategori dahulu —</option>');
                        sub.disabled = true;
                        return;
                    }
                    sub.disabled = false;
                    sub.insertAdjacentHTML('beforeend', '<option value="">— Tanpa sub kategori —</option>');
                    list.forEach(s => {
                        const sel = String(s.id) === String(preselect) ? ' selected' : '';
                        sub.insertAdjacentHTML('beforeend', `<option value="${s.id}"${sel}>${s.nama}</option>`);
                    });
                };

                root.addEventListener('change', () => refresh());
                refresh(sub.dataset.old);
            });

            // Foto produk: preview + validasi tipe & ukuran sebelum submit
            (function () {
                const input   = document.getElementById('gambar-input');
                const preview = document.getElementById('gambar-preview');
                const info    = document.getElementById('gambar-info');
                if (!input) return;

                const ALLOWED = ['image/jpeg','image/png','image/webp'];
                const MAX_BYTES = 4 * 1024 * 1024;

                input.addEventListener('change', () => {
                    info.textContent = '';
                    input.classList.remove('is-invalid');
                    const file = input.files?.[0];
                    if (!file) {
                        preview.innerHTML = '<div class="text-center small px-2"><i class="ti ti-photo" style="font-size:2rem"></i><div>Belum ada gambar</div></div>';
                        return;
                    }
                    if (!ALLOWED.includes(file.type)) {
                        input.classList.add('is-invalid');
                        info.innerHTML = '<span class="text-danger"><i class="ti ti-alert-circle me-1"></i>Format harus JPG, PNG, atau WEBP.</span>';
                        input.value = '';
                        return;
                    }
                    if (file.size > MAX_BYTES) {
                        input.classList.add('is-invalid');
                        info.innerHTML = '<span class="text-danger"><i class="ti ti-alert-circle me-1"></i>Ukuran maksimal 4 MB (file Anda '+(file.size/1024/1024).toFixed(2)+' MB).</span>';
                        input.value = '';
                        return;
                    }
                    const url = URL.createObjectURL(file);
                    preview.innerHTML = '<img src="'+url+'" alt="preview" style="width:100%;height:100%;object-fit:cover">';
                    info.innerHTML = '<i class="ti ti-circle-check text-success me-1"></i>'+file.name+' &middot; '+(file.size/1024).toFixed(0)+' KB';
                });

                document.getElementById('produk-form')?.addEventListener('submit', (e) => {
                    if (!input.files || !input.files[0]) {
                        e.preventDefault();
                        input.classList.add('is-invalid');
                        info.innerHTML = '<span class="text-danger"><i class="ti ti-alert-circle me-1"></i>Foto produk wajib diunggah.</span>';
                        input.scrollIntoView({behavior:'smooth', block:'center'});
                    }
                });
            })();

            // Foto tambahan: akumulasi multi-pick + thumbnail dengan tombol hapus.
            // Browser secara default mengganti seluruh selection setiap kali user
            // membuka file picker. Kita akali dengan menyimpan File[] kita sendiri
            // dan menulis ulang `input.files` lewat DataTransfer.
            window.spht_setupExtraImages?.(document.getElementById('gambar-extra-input'), {
                previewEl: document.getElementById('gambar-extra-preview'),
                maxFiles: 5,
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
