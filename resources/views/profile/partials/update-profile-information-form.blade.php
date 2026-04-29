@php
    $wilayahTree = \App\Support\Wilayah::tree();
@endphp
<section>
    <header>
        <h3 class="card-title">Informasi Profil</h3>
        <p class="text-secondary small">
            Perbarui data akun &amp; alamat. Wilayah administratif (provinsi, kota/kabupaten,
            kecamatan) digunakan untuk simulasi perhitungan ongkos kirim.
        </p>
    </header>

    <form method="POST" action="{{ route('verification.send') }}" id="send-verification">
        @csrf
    </form>

    <form method="POST" action="{{ route('profile.update') }}" class="mt-3">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label">Nama</label>
            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control @error('name') is-invalid @enderror" required autofocus autocomplete="name">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control @error('email') is-invalid @enderror" required autocomplete="username">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2 small text-secondary">
                    Email Anda belum terverifikasi.
                    <button form="send-verification" class="btn btn-link p-0 align-baseline">Klik di sini untuk kirim ulang email verifikasi.</button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <div class="mt-2 small text-success">Tautan verifikasi baru telah dikirim ke email Anda.</div>
                @endif
            @endif
        </div>

        <div class="mb-3">
            <label for="no_hp" class="form-label">No. HP</label>
            <input id="no_hp" type="text" name="no_hp" value="{{ old('no_hp', $user->no_hp) }}" class="form-control @error('no_hp') is-invalid @enderror" autocomplete="tel">
            @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <hr>
        <h4 class="mb-2">Alamat</h4>
        <p class="text-secondary small mb-3">
            @if ($user->isPetani())
                Alamat ini merupakan alamat <strong>toko</strong> Anda — pembeli akan menghitung ongkir berdasarkan lokasi ini.
            @else
                Alamat ini merupakan alamat <strong>pengiriman</strong> Anda — ongkir akan dihitung berdasarkan lokasi ini.
            @endif
        </p>

        <div class="row g-2">
            <div class="col-md-4">
                <label for="province_id" class="form-label">Provinsi</label>
                <select id="province_id" name="province_id" class="form-select @error('province_id') is-invalid @enderror" data-wilayah="province">
                    <option value="">— Pilih provinsi —</option>
                    @foreach ($wilayahTree as $prov)
                        <option value="{{ $prov['id'] }}" @selected(old('province_id', $user->province_id) === $prov['id'])>{{ $prov['name'] }}</option>
                    @endforeach
                </select>
                @error('province_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label for="city_id" class="form-label">Kota/Kabupaten</label>
                <select id="city_id" name="city_id" class="form-select @error('city_id') is-invalid @enderror" data-wilayah="city" data-current="{{ old('city_id', $user->city_id) }}">
                    <option value="">— Pilih kota —</option>
                </select>
                @error('city_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-4">
                <label for="district_id" class="form-label">Kecamatan</label>
                <select id="district_id" name="district_id" class="form-select @error('district_id') is-invalid @enderror" data-wilayah="district" data-current="{{ old('district_id', $user->district_id) }}">
                    <option value="">— Pilih kecamatan —</option>
                </select>
                @error('district_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="mt-3">
            <label for="alamat" class="form-label">Alamat Lengkap</label>
            <textarea id="alamat" name="alamat" rows="3" class="form-control @error('alamat') is-invalid @enderror" placeholder="Nama jalan, no. rumah, RT/RW, patokan...">{{ old('alamat', $user->alamat) }}</textarea>
            @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="d-flex align-items-center gap-2 mt-3">
            <button type="submit" class="btn btn-primary">Simpan</button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small">Tersimpan.</span>
            @endif
        </div>
    </form>
</section>

@push('scripts')
<script>
    (function () {
        const tree = @json($wilayahTree);
        const provSel = document.getElementById('province_id');
        const citySel = document.getElementById('city_id');
        const distSel = document.getElementById('district_id');
        if (!provSel || !citySel || !distSel) return;

        const findProv = id => tree.find(p => p.id === id);
        const findCity = (prov, id) => prov ? prov.cities.find(c => c.id === id) : null;

        function renderCities(preserveCurrent) {
            const prov = findProv(provSel.value);
            const current = preserveCurrent ? citySel.dataset.current : '';
            citySel.innerHTML = '<option value="">— Pilih kota —</option>';
            if (!prov) { renderDistricts(false); return; }
            prov.cities.forEach(c => {
                const opt = document.createElement('option');
                opt.value = c.id;
                opt.textContent = c.name;
                if (c.id === current) opt.selected = true;
                citySel.appendChild(opt);
            });
            renderDistricts(preserveCurrent);
        }

        function renderDistricts(preserveCurrent) {
            const prov = findProv(provSel.value);
            const city = findCity(prov, citySel.value);
            const current = preserveCurrent ? distSel.dataset.current : '';
            distSel.innerHTML = '<option value="">— Pilih kecamatan —</option>';
            if (!city) return;
            city.districts.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.id;
                opt.textContent = d.name;
                if (d.id === current) opt.selected = true;
                distSel.appendChild(opt);
            });
        }

        provSel.addEventListener('change', () => renderCities(false));
        citySel.addEventListener('change', () => renderDistricts(false));

        // Inisialisasi awal — jaga nilai lama dari old()/database.
        renderCities(true);
    })();
</script>
@endpush
