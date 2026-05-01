@php
    /** @var \App\Models\HeroSlide $slide */
    $isEdit = $slide->exists;
    $title  = $isEdit ? 'Ubah Hero Banner' : 'Tambah Hero Banner';
    $active = 'admin.hero';
    $action = $isEdit ? route('admin.hero.update', $slide) : route('admin.hero.store');
@endphp

<x-layouts.app :title="$title" :active="$active">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $title }}</h3>
                </div>
                <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
                    @csrf
                    @if ($isEdit) @method('PUT') @endif

                    <div class="card-body">
                        <x-form-errors title="Hero banner gagal disimpan" />

                        <div class="mb-3">
                            <label class="form-label required">Judul</label>
                            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                                   value="{{ old('title', $slide->title) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Sub-judul</label>
                            <textarea name="subtitle" rows="2" class="form-control @error('subtitle') is-invalid @enderror">{{ old('subtitle', $slide->subtitle) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gambar Banner</label>
                            @if ($slide->image)
                                <div class="mb-2">
                                    <img src="{{ $slide->image_url }}" alt="" class="rounded border"
                                         style="max-width:320px;width:100%;height:140px;object-fit:cover;background:#f6f8fa">
                                </div>
                            @endif
                            <input type="file" name="image" accept="image/*"
                                   class="form-control @error('image') is-invalid @enderror">
                            <div class="form-text">Format JPG/PNG, maks 4 MB. Rasio yang disarankan 16:6 atau lebih lebar.</div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Label Tombol (CTA)</label>
                                <input type="text" name="cta_label" class="form-control @error('cta_label') is-invalid @enderror"
                                       value="{{ old('cta_label', $slide->cta_label) }}" placeholder="mis. Belanja Sekarang">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">URL Tombol</label>
                                <input type="text" name="cta_url" class="form-control @error('cta_url') is-invalid @enderror"
                                       value="{{ old('cta_url', $slide->cta_url) }}" placeholder="mis. /katalog?sort=terpopuler">
                            </div>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label">Urutan Tampil</label>
                                <input type="number" name="sort_order" min="0" class="form-control"
                                       value="{{ old('sort_order', $slide->sort_order ?? 0) }}">
                                <div class="form-text">Angka kecil tampil lebih dulu.</div>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <label class="form-check form-switch mb-0">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                           @checked(old('is_active', $slide->is_active ?? true))>
                                    <span class="form-check-label">Aktif (tampilkan di katalog)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <a href="{{ route('admin.hero.index') }}" class="btn btn-link me-2">Batal</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ti ti-device-floppy me-1"></i> {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Banner' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
