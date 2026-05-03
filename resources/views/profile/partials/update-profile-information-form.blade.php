@php
    use App\Support\Wilayah;
    use App\Http\Controllers\Pelanggan\AlamatController;

    $isPelanggan = $user->isPelanggan();
    $isPetani    = $user->isPetani();

    // Untuk PETANI form alamat tunggal (tetap di kolom users.*).
    // Untuk PELANGGAN form di-handle oleh address book di section terpisah.
    $petaniProvinceId = old('province_id', $user->province_id);
    $petaniCityId     = old('city_id',     $user->city_id);
    $petaniDistrictId = old('district_id', $user->district_id);

    $provinces = Wilayah::provinces();
    $petaniCities    = $petaniProvinceId ? Wilayah::cities($petaniProvinceId) : [];
    $petaniDistricts = $petaniCityId     ? Wilayah::districts($petaniCityId)  : [];

    // Address book pelanggan (max 3, lihat AlamatController::MAX_ADDRESSES).
    $addresses    = $isPelanggan ? $user->addresses()->get() : collect();
    $maxAddresses = AlamatController::MAX_ADDRESSES;
    $canAddMore   = $isPelanggan && $addresses->count() < $maxAddresses;
@endphp

<div class="pf-head">
    <span class="ico"><i class="ti ti-user-circle"></i></span>
    <div class="flex-fill">
        <h3 class="title">Identitas &amp; Alamat</h3>
        <div class="sub">Data pribadi dan alamat untuk pengiriman/penjualan</div>
    </div>
    @if (session('status') === 'profile-updated')
        <span class="badge bg-success-lt text-success border-0"><i class="ti ti-circle-check me-1"></i>Tersimpan</span>
    @endif
</div>

<form method="POST" action="{{ route('verification.send') }}" id="send-verification">
    @csrf
</form>

{{-- =================== Form profil utama (identitas) =================== --}}
<form method="POST" action="{{ route('profile.update') }}" class="pf-body">
    @csrf
    @method('patch')

    <div class="pf-section-label">Data Pribadi</div>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="name" class="form-label required">Nama Lengkap</label>
            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}"
                   class="form-control @error('name') is-invalid @enderror" required autocomplete="name">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="col-md-6">
            <label for="email" class="form-label required">Email</label>
            <div class="input-group">
                <span class="input-group-text"><i class="ti ti-mail"></i></span>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="form-control @error('email') is-invalid @enderror" required autocomplete="username">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-1 small text-secondary">
                    <i class="ti ti-alert-triangle text-warning me-1"></i>Email belum terverifikasi.
                    <button form="send-verification" class="btn btn-link p-0 align-baseline">Kirim ulang tautan verifikasi</button>
                </div>
                @if (session('status') === 'verification-link-sent')
                    <div class="mt-1 small text-success"><i class="ti ti-circle-check me-1"></i>Tautan verifikasi baru telah dikirim.</div>
                @endif
            @endif
        </div>

        <div class="col-md-6">
            <label for="no_hp" class="form-label required">No. HP</label>
            <div class="input-group">
                <span class="input-group-text"><i class="ti ti-phone"></i></span>
                <input id="no_hp" type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}"
                       class="form-control @error('no_hp') is-invalid @enderror" required autocomplete="tel"
                       placeholder="08xx-xxxx-xxxx">
                @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    @if ($isPetani)
        <hr class="pf-section-divider">

        <div class="pf-section-label d-flex align-items-center justify-content-between">
            <span>Alamat Toko</span>
            @if ($user->hasCompleteAddress())
                <span class="badge bg-success-lt text-success border-0"><i class="ti ti-circle-check me-1"></i>Lengkap</span>
            @else
                <span class="badge bg-warning-lt text-warning border-0"><i class="ti ti-alert-triangle me-1"></i>Belum lengkap</span>
            @endif
        </div>

        <p class="small text-secondary mb-3">
            <i class="ti ti-info-circle me-1"></i>
            Alamat ini menjadi <strong>lokasi toko</strong> Anda — pembeli akan menghitung ongkos kirim berdasarkan kota/kecamatan ini.
        </p>

        <div class="row g-3" data-wilayah-group="petani">
            <div class="col-md-4">
                <label for="province_id" class="form-label required">Provinsi</label>
                <select id="province_id" name="province_id" class="form-select @error('province_id') is-invalid @enderror" data-cascade="province">
                    <option value="">— Pilih provinsi —</option>
                    @foreach ($provinces as $prov)
                        <option value="{{ $prov['id'] }}" @selected($petaniProvinceId === $prov['id'])>{{ $prov['name'] }}</option>
                    @endforeach
                </select>
                @error('province_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label for="city_id" class="form-label required">Kota/Kabupaten</label>
                <select id="city_id" name="city_id" class="form-select @error('city_id') is-invalid @enderror" data-cascade="city">
                    <option value="">— Pilih kota —</option>
                    @foreach ($petaniCities as $c)
                        <option value="{{ $c['id'] }}" @selected($petaniCityId === $c['id'])>{{ $c['name'] }}</option>
                    @endforeach
                </select>
                @error('city_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label for="district_id" class="form-label required">Kecamatan</label>
                <select id="district_id" name="district_id" class="form-select @error('district_id') is-invalid @enderror" data-cascade="district">
                    <option value="">— Pilih kecamatan —</option>
                    @foreach ($petaniDistricts as $d)
                        <option value="{{ $d['id'] }}" @selected($petaniDistrictId === $d['id'])>{{ $d['name'] }}</option>
                    @endforeach
                </select>
                @error('district_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12">
                <label for="alamat" class="form-label required">Alamat Lengkap</label>
                <textarea id="alamat" name="alamat" rows="3"
                          class="form-control @error('alamat') is-invalid @enderror" required
                          placeholder="Nama jalan, no. rumah, RT/RW, patokan, kode pos...">{{ old('alamat', $user->alamat) }}</textarea>
                @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    @endif

    <div class="d-flex align-items-center justify-content-between mt-4">
        <div class="text-secondary small">
            <i class="ti ti-shield-lock me-1"></i>
            Data Anda hanya digunakan untuk operasional marketplace.
        </div>
        <button type="submit" class="btn btn-success px-4">
            <i class="ti ti-device-floppy me-1"></i> Simpan Perubahan
        </button>
    </div>
</form>

{{-- =================== Address book khusus pelanggan =================== --}}
@if ($isPelanggan)
    <div class="pf-body" style="padding-top:0">
        <hr class="pf-section-divider">

        <div class="pf-section-label d-flex align-items-center justify-content-between">
            <span>Alamat Pengiriman</span>
            <span class="text-secondary small fw-normal">{{ $addresses->count() }}/{{ $maxAddresses }} tersimpan</span>
        </div>

        <p class="small text-secondary mb-3">
            <i class="ti ti-info-circle me-1"></i>
            Anda bisa menyimpan hingga <strong>{{ $maxAddresses }} alamat</strong>. Alamat <strong>utama</strong> dipakai sebagai default saat checkout.
        </p>

        @if ($addresses->isEmpty())
            <div class="alert alert-warning py-2 small mb-3">
                <i class="ti ti-alert-triangle me-1"></i>
                Anda belum menyimpan alamat. Tambahkan minimal satu alamat pengiriman.
            </div>
        @else
            <div class="row g-3 mb-3">
                @foreach ($addresses as $addr)
                    <div class="col-md-6">
                        <div class="card h-100 {{ $addr->is_default ? 'border-success' : '' }}">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                                    <div>
                                        <span class="fw-bold">{{ $addr->label ?: 'Alamat' }}</span>
                                        @if ($addr->is_default)
                                            <span class="badge bg-success-lt text-success border-0 ms-1"><i class="ti ti-star-filled me-1"></i>Utama</span>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-1">
                                        @if (! $addr->is_default)
                                            <form method="POST" action="{{ route('pelanggan.alamat.default', $addr) }}">
                                                @csrf @method('patch')
                                                <button class="btn btn-sm btn-outline-success" title="Jadikan alamat utama">
                                                    <i class="ti ti-star"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('pelanggan.alamat.destroy', $addr) }}"
                                              onsubmit="return confirm('Hapus alamat ini?')">
                                            @csrf @method('delete')
                                            <button class="btn btn-sm btn-outline-danger" title="Hapus alamat">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="small">
                                    <div class="fw-semibold">{{ $addr->nama_penerima }}</div>
                                    <div class="text-secondary">{{ $addr->no_hp_penerima }}</div>
                                    <div class="mt-1">{{ $addr->alamat }}</div>
                                    <div class="text-secondary mt-1">
                                        {{ $addr->district_name }}, {{ $addr->city_name }}, {{ $addr->province_name }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($canAddMore)
            <details class="mb-2" @if ($addresses->isEmpty() || $errors->any()) open @endif>
                <summary class="btn btn-outline-success">
                    <i class="ti ti-plus me-1"></i> Tambah Alamat Baru
                </summary>

                <form method="POST" action="{{ route('pelanggan.alamat.store') }}" class="mt-3">
                    @csrf

                    <div class="row g-3" data-wilayah-group="alamat-new">
                        <div class="col-md-4">
                            <label for="addr_label" class="form-label">Label (opsional)</label>
                            <input id="addr_label" type="text" name="label" value="{{ old('label') }}"
                                   class="form-control @error('label') is-invalid @enderror"
                                   placeholder="Rumah / Kantor / Kos">
                            @error('label') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="addr_nama" class="form-label required">Nama Penerima</label>
                            <input id="addr_nama" type="text" name="nama_penerima" value="{{ old('nama_penerima', $user->name) }}"
                                   class="form-control @error('nama_penerima') is-invalid @enderror" required>
                            @error('nama_penerima') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="addr_hp" class="form-label required">No. HP Penerima</label>
                            <input id="addr_hp" type="text" name="no_hp_penerima" value="{{ old('no_hp_penerima', $user->no_hp) }}"
                                   class="form-control @error('no_hp_penerima') is-invalid @enderror" required
                                   placeholder="08xx-xxxx-xxxx">
                            @error('no_hp_penerima') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="addr_province" class="form-label required">Provinsi</label>
                            <select id="addr_province" name="province_id"
                                    class="form-select @error('province_id') is-invalid @enderror"
                                    data-cascade="province" required>
                                <option value="">— Pilih provinsi —</option>
                                @foreach ($provinces as $prov)
                                    <option value="{{ $prov['id'] }}" @selected(old('province_id') === $prov['id'])>{{ $prov['name'] }}</option>
                                @endforeach
                            </select>
                            @error('province_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="addr_city" class="form-label required">Kota/Kabupaten</label>
                            <select id="addr_city" name="city_id"
                                    class="form-select @error('city_id') is-invalid @enderror"
                                    data-cascade="city" required>
                                <option value="">— Pilih kota —</option>
                            </select>
                            @error('city_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="addr_district" class="form-label required">Kecamatan</label>
                            <select id="addr_district" name="district_id"
                                    class="form-select @error('district_id') is-invalid @enderror"
                                    data-cascade="district" required>
                                <option value="">— Pilih kecamatan —</option>
                            </select>
                            @error('district_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label for="addr_alamat" class="form-label required">Alamat Lengkap</label>
                            <textarea id="addr_alamat" name="alamat" rows="2" required
                                      class="form-control @error('alamat') is-invalid @enderror"
                                      placeholder="Nama jalan, no. rumah, RT/RW, patokan, kode pos...">{{ old('alamat') }}</textarea>
                            @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-check">
                                <input type="hidden" name="is_default" value="0">
                                <input type="checkbox" name="is_default" value="1" class="form-check-input"
                                       @checked(old('is_default') || $addresses->isEmpty())
                                       @disabled($addresses->isEmpty())>
                                <span class="form-check-label">
                                    Jadikan alamat utama
                                    @if ($addresses->isEmpty())
                                        <span class="text-secondary small">(otomatis untuk alamat pertama)</span>
                                    @endif
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3">
                        <button type="submit" class="btn btn-success">
                            <i class="ti ti-device-floppy me-1"></i> Simpan Alamat
                        </button>
                    </div>
                </form>
            </details>
        @else
            <div class="alert alert-info py-2 small">
                <i class="ti ti-info-circle me-1"></i>
                Anda sudah mencapai batas maksimal {{ $maxAddresses }} alamat. Hapus salah satu untuk menambah baru.
            </div>
        @endif
    </div>
@endif

@push('scripts')
<script>
    (function () {
        const citiesUrl    = (id) => @json(url('/wilayah/cities')) + '/' + encodeURIComponent(id);
        const districtsUrl = (id) => @json(url('/wilayah/districts')) + '/' + encodeURIComponent(id);

        function reset(select, placeholder) {
            select.innerHTML = '<option value="">' + placeholder + '</option>';
        }

        function fillOptions(select, items) {
            items.forEach(it => {
                const opt = document.createElement('option');
                opt.value = it.id;
                opt.textContent = it.name;
                select.appendChild(opt);
            });
        }

        // Pasang cascading di setiap container yang punya data-wilayah-group.
        document.querySelectorAll('[data-wilayah-group]').forEach(group => {
            const provSel = group.querySelector('[data-cascade="province"]');
            const citySel = group.querySelector('[data-cascade="city"]');
            const distSel = group.querySelector('[data-cascade="district"]');
            if (!provSel || !citySel || !distSel) return;

            async function loadCities() {
                reset(citySel, '— Pilih kota —');
                reset(distSel, '— Pilih kecamatan —');
                if (!provSel.value) return;
                citySel.disabled = true;
                try {
                    const res = await fetch(citiesUrl(provSel.value), { headers: { Accept: 'application/json' } });
                    if (!res.ok) return;
                    fillOptions(citySel, await res.json());
                } finally {
                    citySel.disabled = false;
                }
            }

            async function loadDistricts() {
                reset(distSel, '— Pilih kecamatan —');
                if (!citySel.value) return;
                distSel.disabled = true;
                try {
                    const res = await fetch(districtsUrl(citySel.value), { headers: { Accept: 'application/json' } });
                    if (!res.ok) return;
                    fillOptions(distSel, await res.json());
                } finally {
                    distSel.disabled = false;
                }
            }

            provSel.addEventListener('change', loadCities);
            citySel.addEventListener('change', loadDistricts);
        });
    })();
</script>
@endpush
