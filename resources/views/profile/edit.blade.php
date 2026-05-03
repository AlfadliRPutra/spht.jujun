@php
    use App\Enums\UserRole;
    use App\Support\Wilayah;

    $user        = auth()->user();
    $isPelanggan = $user->role === UserRole::Pelanggan;
    $layout      = $isPelanggan ? 'layouts.storefront' : 'layouts.app';

    // Inisial untuk avatar fallback.
    $initials = collect(explode(' ', trim($user->name)))->filter()->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');

    // Hitung kelengkapan profil — feedback ringan, tidak memblokir aksi apapun.
    // Setiap entri punya target (anchor field atau route) supaya item yang
    // belum lengkap bisa diklik langsung ke tempat ngisinya.
    $checks = [
        ['label' => 'Nama lengkap',  'hint' => 'Tampil di toko / pesanan',     'done' => filled($user->name),  'href' => '#name',  'external' => false],
        ['label' => 'Email aktif',   'hint' => 'Untuk notifikasi & login',     'done' => filled($user->email), 'href' => '#email', 'external' => false],
        ['label' => 'Nomor telepon', 'hint' => 'Format 08xx-xxxx-xxxx',        'done' => filled($user->no_hp), 'href' => '#no_hp', 'external' => false],
    ];

    if ($isPelanggan) {
        // Pelanggan menyimpan alamat di tabel `addresses`. Kolom users.alamat
        // tidak dipakai role ini, jadi tidak perlu dicek.
        $checks[] = [
            'label'    => 'Alamat pengiriman',
            'hint'     => 'Minimal satu alamat tersimpan',
            'done'     => $user->addresses()->exists(),
            'href'     => '#sec-identitas',
            'external' => false,
        ];
    } elseif ($user->role === UserRole::Petani) {
        $hasWilayah = filled($user->province_id) && filled($user->city_id) && filled($user->district_id);
        $checks[] = ['label' => 'Wilayah toko',     'hint' => 'Provinsi · kota · kecamatan',    'done' => $hasWilayah,                  'href' => '#province_id',  'external' => false];
        $checks[] = ['label' => 'Alamat detail',    'hint' => 'Jalan, RT/RW, patokan',          'done' => filled($user->alamat),        'href' => '#alamat',       'external' => false];
        // nama_usaha & nik diisi di halaman Verifikasi Akun, bukan di sini.
        $checks[] = ['label' => 'Nama usaha',       'hint' => 'Diisi di halaman Verifikasi',    'done' => filled($user->nama_usaha),    'href' => route('petani.verifikasi.index'), 'external' => true];
        $checks[] = ['label' => 'NIK (16 digit)',   'hint' => 'Diisi di halaman Verifikasi',    'done' => filled($user->nik),           'href' => route('petani.verifikasi.index'), 'external' => true];
    }

    $totalChecks   = count($checks);
    $doneChecks    = collect($checks)->where('done', true)->count();
    $progress      = $totalChecks ? (int) round($doneChecks / $totalChecks * 100) : 0;
    $missingChecks = collect($checks)->where('done', false)->values();

    [$roleChip, $roleIcon] = match ($user->role) {
        UserRole::Petani    => ['Petani Mitra',  'plant-2'],
        UserRole::Admin     => ['Administrator', 'shield-lock'],
        default             => ['Pelanggan',     'user-heart'],
    };
@endphp

<x-dynamic-component :component="$layout" title="Profil">
    @push('styles')
        <style>
            /* HERO */
            .profile-hero{
                position:relative;border:1px solid var(--border);border-radius: var(--radius-lg);
                background:
                    radial-gradient(900px 280px at 100% -10%, rgba(16,185,129,.18), transparent 60%),
                    radial-gradient(700px 260px at -10% 110%, rgba(99,102,241,.14), transparent 55%),
                    linear-gradient(135deg,#ffffff 0%,#f7f9fc 100%);
                box-shadow: var(--shadow-sm);
                padding:1.5rem 1.75rem;
                overflow:hidden;
            }
            .profile-hero::before{
                content:"";position:absolute;inset:0;
                background: repeating-linear-gradient(45deg, rgba(15,23,42,.02) 0 1px, transparent 1px 14px);
                pointer-events:none;
            }
            .pf-avatar{
                width:88px;height:88px;border-radius:50%;
                background: linear-gradient(135deg, var(--brand-500), var(--brand-700));
                color:#fff;font-weight:800;font-size:1.7rem;letter-spacing:.5px;
                display:inline-flex;align-items:center;justify-content:center;
                box-shadow:0 12px 26px -10px rgba(16,185,129,.55);
                position:relative;
            }
            .pf-avatar .pf-edit{
                position:absolute;right:-2px;bottom:-2px;
                width:30px;height:30px;border-radius:50%;
                background:#fff;color:var(--brand-700);
                display:inline-flex;align-items:center;justify-content:center;
                box-shadow: 0 4px 10px rgba(15,23,42,.15);
                border:1px solid var(--border);font-size:.95rem;
            }
            .pf-rolechip{
                display:inline-flex;align-items:center;gap:.35rem;
                padding:.35rem .7rem;border-radius:999px;
                font-size:.74rem;font-weight:700;letter-spacing:.04em;
                background: var(--brand-50);color: var(--brand-700);
                border:1px solid var(--brand-200);
            }
            .pf-stats{ display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.5rem; }
            .pf-stat{
                font-size:.78rem;color:var(--muted);
                display:inline-flex;align-items:center;gap:.3rem;
            }
            .pf-progress{
                background:#fff;border:1px solid var(--border);border-radius: var(--radius);
                padding:.85rem 1rem;min-width:280px;max-width:360px;
            }
            .pf-progress .bar{ height:8px;border-radius:99px;background:#eef2f7;overflow:hidden; }
            .pf-progress .bar > span{
                display:block;height:100%;
                background: linear-gradient(90deg,var(--brand-500),var(--brand-700));
                transition: width .6s ease;
            }
            .pf-progress .bar.is-full > span{
                background: linear-gradient(90deg,#10b981,#16a34a);
            }
            .pf-progress .label{ font-size:.78rem;color: var(--muted);font-weight:600; }
            .pf-progress .pct{ font-size:1.05rem;font-weight:800;color: var(--ink); }
            .pf-progress .pct.is-full{ color: var(--brand-700); }

            /* Checklist kelengkapan */
            .pf-checklist{
                list-style:none;padding:0;margin:.6rem 0 0;
                display:flex;flex-direction:column;gap:.25rem;
            }
            .pf-check{
                display:flex;align-items:center;gap:.5rem;
                font-size:.82rem;line-height:1.3;
                padding:.3rem .15rem;border-radius:.4rem;
            }
            .pf-check .ico{
                width:18px;height:18px;flex-shrink:0;
                display:inline-flex;align-items:center;justify-content:center;
                font-size:1rem;
            }
            .pf-check.is-done       { color: var(--ink-2); }
            .pf-check.is-done .ico  { color:#16a34a; }
            .pf-check.is-done .lbl  { text-decoration: line-through; text-decoration-color: rgba(22,163,74,.45); }
            .pf-check.is-pending    { color: var(--ink); background: #fff7ed; }
            .pf-check.is-pending .ico { color:#ea580c; }
            .pf-check .lbl{ flex:1;font-weight:600; }
            .pf-check .hint{ color: var(--muted);font-weight:400;font-size:.74rem; }
            .pf-check .link{
                font-size:.72rem;font-weight:700;text-decoration:none;
                color: var(--brand-700);background: var(--brand-50);
                border:1px solid var(--brand-200);border-radius:99px;
                padding:.1rem .55rem;white-space:nowrap;
                display:inline-flex;align-items:center;gap:.2rem;
            }
            .pf-check .link:hover{ background: var(--brand-100); }
            .pf-check .link i{ font-size:.85rem; }
            .pf-checklist.is-collapsed{ display:none; }
            .pf-toggle{
                background:transparent;border:0;padding:.25rem 0;
                font-size:.74rem;font-weight:700;color: var(--brand-700);
                display:inline-flex;align-items:center;gap:.25rem;cursor:pointer;
                margin-top:.4rem;
            }
            .pf-toggle:hover{ text-decoration: underline; }

            /* Highlight efek saat anchor field di-klik */
            .pf-highlight{
                animation: pfHighlight 1.4s ease;
                box-shadow: 0 0 0 3px rgba(16,185,129,.35) !important;
            }
            @keyframes pfHighlight {
                0%   { box-shadow: 0 0 0 0   rgba(16,185,129,.55); }
                40%  { box-shadow: 0 0 0 8px rgba(16,185,129,.35); }
                100% { box-shadow: 0 0 0 3px rgba(16,185,129,0);   }
            }

            /* SIDE NAV */
            .pf-side{
                position:sticky;top:84px;
                background:#fff;border:1px solid var(--border);
                border-radius: var(--radius-lg);
                padding:.5rem;box-shadow: var(--shadow-xs);
            }
            .pf-side-item{
                display:flex;align-items:center;gap:.7rem;
                padding:.65rem .8rem;border-radius: var(--radius-sm);
                color: var(--ink-2);text-decoration:none;font-weight:500;
                transition: background .15s, color .15s;
            }
            .pf-side-item:hover{ background: var(--brand-50);color: var(--brand-700); }
            .pf-side-item.is-active{
                background: var(--brand-50);color: var(--brand-700);
                font-weight:700;box-shadow: inset 0 0 0 1px var(--brand-200);
            }
            .pf-side-item .ico{
                width:32px;height:32px;border-radius:.6rem;flex-shrink:0;
                display:inline-flex;align-items:center;justify-content:center;
                background:#f4f6fb;color:var(--ink-2);
            }
            .pf-side-item:hover .ico, .pf-side-item.is-active .ico{
                background: var(--brand-100);color: var(--brand-700);
            }
            .pf-side-item.is-danger:hover .ico, .pf-side-item.is-danger.is-active .ico{
                background:#fee2e2;color:#b91c1c;
            }
            .pf-side-item.is-danger:hover, .pf-side-item.is-danger.is-active{
                background:#fef2f2;color:#b91c1c;box-shadow: inset 0 0 0 1px #fecaca;
            }

            /* SECTION CARDS */
            .pf-card{
                border:1px solid var(--border);border-radius: var(--radius-lg);
                background:#fff;box-shadow: var(--shadow-xs);overflow:hidden;
                scroll-margin-top: 90px;
            }
            .pf-card .pf-head{
                padding:1.1rem 1.4rem;border-bottom:1px solid var(--border-2);
                display:flex;align-items:center;gap:.9rem;
                background: linear-gradient(180deg,#fbfcfe 0%,#fff 100%);
                position:relative;
            }
            .pf-card .pf-head::before{
                content:"";position:absolute;left:0;top:0;bottom:0;width:4px;
                background: linear-gradient(180deg, var(--brand-500), var(--brand-700));
            }
            .pf-card.is-danger .pf-head::before{ background: linear-gradient(180deg,#ef4444,#b91c1c); }
            .pf-head .ico{
                width:44px;height:44px;border-radius:.85rem;
                display:inline-flex;align-items:center;justify-content:center;
                background: linear-gradient(135deg, var(--brand-100), var(--brand-200));
                color: var(--brand-700);
                box-shadow: inset 0 0 0 1px rgba(16,185,129,.15);
            }
            .pf-card.is-danger .pf-head .ico{
                background: linear-gradient(135deg,#fee2e2,#fecaca); color:#b91c1c;
                box-shadow: inset 0 0 0 1px rgba(190,18,60,.15);
            }
            .pf-head .ico i{ font-size:1.4rem;line-height:1; }
            .pf-head .title{ font-size:1.05rem;font-weight:700;color: var(--ink);line-height:1.2;margin:0; }
            .pf-head .sub{ font-size:.78rem;color: var(--muted);margin-top:.15rem; }

            .pf-body{ padding:1.4rem; }
            .pf-section-divider{
                margin:1.5rem -1.4rem;border:0;border-top:1px dashed var(--border);
            }
            .pf-section-label{
                font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                color: var(--muted);margin-bottom:.85rem;
            }

            /* Password input with eye toggle */
            .pf-password{ position:relative; }
            .pf-password .toggle{
                position:absolute;right:8px;top:50%;transform:translateY(-50%);
                background:transparent;border:0;color: var(--muted);width:36px;height:36px;
                border-radius:.5rem;display:inline-flex;align-items:center;justify-content:center;
            }
            .pf-password .toggle:hover{ background:#f1f5f9;color: var(--ink); }
            .pf-password input{ padding-right:46px; }

            /* Danger zone */
            .danger-card{
                background:linear-gradient(180deg,#fff8f8 0%,#fff 100%);
                border:1px dashed #fecaca;border-radius: var(--radius);
                padding:1rem 1.1rem;
            }
            .danger-list{ font-size:.875rem;color:#7f1d1d;padding-left:1.1rem;margin:.5rem 0 .25rem; }
            .danger-list li{ margin-bottom:.2rem; }
        </style>
    @endpush

    {{-- HERO --}}
    <div class="profile-hero mb-3">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <div class="pf-avatar">
                {{ $initials ?: 'U' }}
                <span class="pf-edit" title="Foto profil belum tersedia"><i class="ti ti-camera"></i></span>
            </div>
            <div class="flex-fill" style="min-width:240px">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h1 class="h2 mb-0">{{ $user->name }}</h1>
                    <span class="pf-rolechip"><i class="ti ti-{{ $roleIcon }}"></i> {{ $roleChip }}</span>
                </div>
                <div class="text-secondary"><i class="ti ti-mail me-1"></i>{{ $user->email }}</div>
                <div class="pf-stats">
                    @if ($user->no_hp)
                        <span class="pf-stat"><i class="ti ti-phone"></i>{{ $user->no_hp }}</span>
                    @endif
                    @if ($user->hasCompleteAddress())
                        <span class="pf-stat"><i class="ti ti-map-pin"></i>{{ $user->city_name }}, {{ $user->province_name }}</span>
                    @endif
                    <span class="pf-stat"><i class="ti ti-calendar"></i>Bergabung {{ $user->created_at?->translatedFormat('M Y') }}</span>
                </div>
            </div>

            <div class="pf-progress">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="label">Kelengkapan profil</span>
                    <span class="pct {{ $progress === 100 ? 'is-full' : '' }}">{{ $progress }}%</span>
                </div>
                <div class="bar {{ $progress === 100 ? 'is-full' : '' }}"><span style="width: {{ $progress }}%"></span></div>

                @if ($progress === 100)
                    <div class="text-success small fw-semibold mt-2">
                        <i class="ti ti-circle-check-filled me-1"></i>Semua data sudah lengkap.
                    </div>
                @else
                    <div class="text-secondary small mt-2">
                        Tinggal {{ $totalChecks - $doneChecks }} dari {{ $totalChecks }} item lagi.
                    </div>
                @endif

                {{-- Tampilkan otomatis daftar yang BELUM lengkap; yang sudah selesai
                     disembunyikan agar fokus user pada yang masih harus diisi.
                     Tombol toggle membuka daftar lengkap (termasuk yang sudah ✓). --}}
                @if ($missingChecks->isNotEmpty())
                    <ul class="pf-checklist">
                        @foreach ($missingChecks as $c)
                            <li class="pf-check is-pending">
                                <span class="ico"><i class="ti ti-alert-circle-filled"></i></span>
                                <span class="lbl">
                                    {{ $c['label'] }}
                                    <span class="hint d-block">{{ $c['hint'] }}</span>
                                </span>
                                <a href="{{ $c['href'] }}"
                                   class="link"
                                   @if (! $c['external']) data-pf-jump="{{ $c['href'] }}" @endif>
                                    @if ($c['external'])
                                        <i class="ti ti-external-link"></i>Buka
                                    @else
                                        <i class="ti ti-arrow-right"></i>Lengkapi
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif

                <button type="button" class="pf-toggle" data-pf-toggle-checklist
                        aria-expanded="false" aria-controls="pf-full-checklist">
                    <i class="ti ti-list-details"></i>
                    <span class="label-text">Lihat semua ({{ $doneChecks }}/{{ $totalChecks }} ✓)</span>
                </button>

                <ul class="pf-checklist is-collapsed" id="pf-full-checklist" data-pf-full-list>
                    @foreach ($checks as $c)
                        <li class="pf-check {{ $c['done'] ? 'is-done' : 'is-pending' }}">
                            <span class="ico">
                                <i class="ti ti-{{ $c['done'] ? 'circle-check-filled' : 'alert-circle-filled' }}"></i>
                            </span>
                            <span class="lbl">
                                {{ $c['label'] }}
                                <span class="hint d-block">{{ $c['hint'] }}</span>
                            </span>
                            @if (! $c['done'])
                                <a href="{{ $c['href'] }}"
                                   class="link"
                                   @if (! $c['external']) data-pf-jump="{{ $c['href'] }}" @endif>
                                    @if ($c['external'])
                                        <i class="ti ti-external-link"></i>Buka
                                    @else
                                        <i class="ti ti-arrow-right"></i>Lengkapi
                                    @endif
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- SIDE NAV --}}
        <div class="col-lg-3">
            <div class="pf-side d-flex flex-column gap-1" id="profile-side">
                <a href="#sec-identitas" class="pf-side-item is-active" data-target="sec-identitas">
                    <span class="ico"><i class="ti ti-user-circle"></i></span>
                    <span class="flex-fill">
                        <span class="d-block">Identitas &amp; Alamat</span>
                        <span class="text-secondary small">Profil pribadi</span>
                    </span>
                </a>
                <a href="#sec-keamanan" class="pf-side-item" data-target="sec-keamanan">
                    <span class="ico"><i class="ti ti-lock"></i></span>
                    <span class="flex-fill">
                        <span class="d-block">Keamanan</span>
                        <span class="text-secondary small">Kata sandi</span>
                    </span>
                </a>
                <a href="#sec-akun" class="pf-side-item is-danger" data-target="sec-akun">
                    <span class="ico"><i class="ti ti-trash"></i></span>
                    <span class="flex-fill">
                        <span class="d-block">Hapus Akun</span>
                        <span class="text-secondary small">Zona berbahaya</span>
                    </span>
                </a>
            </div>
        </div>

        {{-- CONTENT --}}
        <div class="col-lg-9">
            <div class="pf-card mb-3" id="sec-identitas">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="pf-card mb-3" id="sec-keamanan">
                @include('profile.partials.update-password-form')
            </div>

            <div class="pf-card is-danger" id="sec-akun">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                const items = document.querySelectorAll('.pf-side-item');
                const sections = ['sec-identitas','sec-keamanan','sec-akun'].map(id => document.getElementById(id));

                // Smooth-scroll dengan offset agar tidak ketutupan header sticky.
                items.forEach(a => a.addEventListener('click', e => {
                    e.preventDefault();
                    const t = document.getElementById(a.dataset.target);
                    if (!t) return;
                    window.scrollTo({ top: t.getBoundingClientRect().top + window.scrollY - 70, behavior: 'smooth' });
                }));

                // Highlight item aktif berdasarkan section yang sedang terlihat.
                const setActive = id => items.forEach(a => a.classList.toggle('is-active', a.dataset.target === id));
                if ('IntersectionObserver' in window) {
                    const io = new IntersectionObserver(entries => {
                        entries.forEach(en => { if (en.isIntersecting) setActive(en.target.id); });
                    }, { rootMargin: '-40% 0px -55% 0px', threshold: 0 });
                    sections.forEach(s => s && io.observe(s));
                }

                // Tombol "Lengkapi" pada checklist: scroll ke field, fokus, beri
                // highlight singkat. Hanya untuk anchor lokal — link eksternal
                // (mis. ke verifikasi petani) dibiarkan jalan normal.
                document.querySelectorAll('[data-pf-jump]').forEach(a => {
                    a.addEventListener('click', e => {
                        const target = a.dataset.pfJump || '';
                        if (! target.startsWith('#')) return; // biarkan navigasi normal
                        const el = document.querySelector(target);
                        if (! el) return;
                        e.preventDefault();
                        window.scrollTo({ top: el.getBoundingClientRect().top + window.scrollY - 90, behavior: 'smooth' });
                        // Focus + highlight setelah animasi scroll selesai
                        setTimeout(() => {
                            if (typeof el.focus === 'function') {
                                try { el.focus({ preventScroll: true }); } catch (_) { el.focus(); }
                            }
                            el.classList.add('pf-highlight');
                            setTimeout(() => el.classList.remove('pf-highlight'), 1400);
                        }, 350);
                    });
                });

                // Toggle daftar lengkap kelengkapan profil
                const toggleBtn  = document.querySelector('[data-pf-toggle-checklist]');
                const fullList   = document.querySelector('[data-pf-full-list]');
                if (toggleBtn && fullList) {
                    toggleBtn.addEventListener('click', () => {
                        const collapsed = fullList.classList.toggle('is-collapsed');
                        toggleBtn.setAttribute('aria-expanded', String(! collapsed));
                        const lbl = toggleBtn.querySelector('.label-text');
                        if (lbl) lbl.textContent = collapsed
                            ? lbl.textContent.replace('Sembunyikan', 'Lihat semua')
                            : lbl.textContent.replace('Lihat semua', 'Sembunyikan');
                    });
                }
            })();
        </script>
    @endpush
</x-dynamic-component>
