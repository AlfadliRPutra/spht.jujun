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
    $checks = [
        'Nama'      => filled($user->name),
        'Email'     => filled($user->email),
        'No. HP'    => filled($user->no_hp),
        'Wilayah'   => $user->hasCompleteAddress(),
        'Alamat'    => filled($user->alamat),
    ];
    if ($user->role === UserRole::Petani) {
        $checks['Nama Usaha'] = filled($user->nama_usaha);
        $checks['NIK']        = filled($user->nik);
    }
    $totalChecks = count($checks);
    $doneChecks  = collect($checks)->filter()->count();
    $progress    = $totalChecks ? (int) round($doneChecks / $totalChecks * 100) : 0;

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
                padding:.85rem 1rem;min-width:240px;
            }
            .pf-progress .bar{ height:8px;border-radius:99px;background:#eef2f7;overflow:hidden; }
            .pf-progress .bar > span{
                display:block;height:100%;
                background: linear-gradient(90deg,var(--brand-500),var(--brand-700));
                transition: width .6s ease;
            }
            .pf-progress .label{ font-size:.78rem;color: var(--muted);font-weight:600; }
            .pf-progress .pct{ font-size:1.05rem;font-weight:800;color: var(--ink); }

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

            <div class="pf-progress text-end">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="label">Kelengkapan profil</span>
                    <span class="pct">{{ $progress }}%</span>
                </div>
                <div class="bar"><span style="width: {{ $progress }}%"></span></div>
                <div class="text-secondary small mt-2">{{ $doneChecks }} dari {{ $totalChecks }} item terisi</div>
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
            })();
        </script>
    @endpush
</x-dynamic-component>
