@php
    /** @var \App\Models\User $petani */
    $title  = 'Detail Verifikasi Petani';
    $active = 'admin.verifikasi';
    $status = $petani->verificationStatus();

    [$badgeClass, $badgeLabel, $badgeIcon] = match ($status) {
        'verified' => ['bg-success-lt text-success border-success-lt', 'Terverifikasi',  'shield-check'],
        'pending'  => ['bg-info-lt text-info border-info-lt',          'Menunggu Review','clock-hour-4'],
        'rejected' => ['bg-danger-lt text-danger border-danger-lt',    'Ditolak',        'circle-x'],
        default    => ['bg-warning-lt text-warning border-warning-lt', 'Belum Diajukan', 'alert-triangle'],
    };

    // Inisial untuk avatar fallback (mis. "Budi Santoso" -> "BS")
    $initials = collect(explode(' ', trim($petani->name)))
        ->filter()->take(2)
        ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
        ->implode('');

    // Timeline progress: Daftar → Ajukan → Review → Selesai
    $steps = [
        ['label' => 'Mendaftar',  'date' => $petani->created_at,                 'done' => true],
        ['label' => 'Mengajukan', 'date' => $petani->verification_submitted_at,  'done' => (bool) $petani->verification_submitted_at || $status === 'verified'],
        ['label' => 'Review',     'date' => null, 'done' => in_array($status, ['verified', 'rejected'], true)],
        ['label' => $status === 'rejected' ? 'Ditolak' : 'Disetujui',
         'date' => null, 'done' => $status === 'verified', 'failed' => $status === 'rejected'],
    ];
@endphp

<x-layouts.app :title="$title" :active="$active">
    @push('styles')
        <style>
            .verif-hero{
                position:relative;
                border:1px solid #e7eaf0;
                border-radius:1rem;
                background:
                    radial-gradient(900px 280px at 100% -10%, rgba(99,102,241,.18), transparent 60%),
                    radial-gradient(700px 260px at -10% 110%, rgba(16,185,129,.18), transparent 55%),
                    linear-gradient(135deg,#ffffff 0%,#f7f9fc 100%);
                box-shadow:0 1px 2px rgba(15,23,42,.04), 0 8px 24px -12px rgba(15,23,42,.08);
                overflow:hidden;
            }
            .verif-hero::before{
                content:"";
                position:absolute; inset:0;
                background:
                    repeating-linear-gradient(45deg, rgba(15,23,42,.02) 0 1px, transparent 1px 14px);
                pointer-events:none;
            }
            .verif-avatar{
                width:84px;height:84px;border-radius:50%;
                display:inline-flex;align-items:center;justify-content:center;
                background:linear-gradient(135deg,#6366f1,#22c55e);
                color:#fff;font-weight:700;font-size:1.6rem;letter-spacing:1px;
                box-shadow:0 8px 24px -8px rgba(99,102,241,.5);
            }
            .verif-badge{
                display:inline-flex;align-items:center;gap:.35rem;
                padding:.4rem .75rem;border-radius:999px;
                font-weight:600;font-size:.8125rem;
                border:1px solid;
            }
            .verif-card{
                border:1px solid #e7eaf0;border-radius:1rem;
                background:#fff;
                box-shadow:0 1px 2px rgba(15,23,42,.04);
                transition:box-shadow .2s ease, transform .2s ease;
                overflow:hidden;
            }
            .verif-card:hover{ box-shadow:0 6px 22px -10px rgba(15,23,42,.18); }

            /* Header card premium: ikon-chip + judul + subtitle, dengan accent stripe atas. */
            .verif-card .card-header{
                position:relative;
                display:flex;align-items:center;gap:.9rem;
                padding:1.1rem 1.25rem;
                border-bottom:1px solid #f1f3f7;
                background:linear-gradient(180deg,#fbfcfe 0%,#ffffff 100%);
            }
            .verif-card .card-header::before{
                content:"";
                position:absolute;left:0;top:0;bottom:0;width:4px;
                background:linear-gradient(180deg,#6366f1,#22c55e);
            }
            .verif-card.accent-success .card-header::before{ background:linear-gradient(180deg,#10b981,#059669); }
            .verif-card.accent-info    .card-header::before{ background:linear-gradient(180deg,#06b6d4,#0891b2); }
            .verif-card.accent-primary .card-header::before{ background:linear-gradient(180deg,#6366f1,#4f46e5); }
            .verif-card.accent-warning .card-header::before{ background:linear-gradient(180deg,#f59e0b,#d97706); }

            .verif-card .header-ico{
                width:44px;height:44px;border-radius:.8rem;flex-shrink:0;
                display:inline-flex;align-items:center;justify-content:center;
                background:linear-gradient(135deg,#eef2ff,#e0e7ff);color:#4f46e5;
                box-shadow:inset 0 0 0 1px rgba(79,70,229,.15);
            }
            .verif-card .header-ico i{ font-size:1.4rem;line-height:1; }
            .verif-card.accent-success .header-ico{ background:linear-gradient(135deg,#dcfce7,#bbf7d0);color:#15803d;box-shadow:inset 0 0 0 1px rgba(21,128,61,.15); }
            .verif-card.accent-info    .header-ico{ background:linear-gradient(135deg,#cffafe,#a5f3fc);color:#0891b2;box-shadow:inset 0 0 0 1px rgba(8,145,178,.18); }
            .verif-card.accent-warning .header-ico{ background:linear-gradient(135deg,#fef3c7,#fde68a);color:#b45309;box-shadow:inset 0 0 0 1px rgba(180,83,9,.18); }

            .verif-card .header-text{ flex:1;min-width:0; }
            .verif-card .header-text .h-title{
                font-size:1.05rem;font-weight:700;color:#0f172a;line-height:1.2;margin:0;
            }
            .verif-card .header-text .h-sub{
                font-size:.78rem;color:#64748b;margin-top:.15rem;
            }
            .verif-card .header-action{ flex-shrink:0; }

            .verif-list{ padding:.5rem; }
            .verif-row{
                display:grid;
                grid-template-columns:52px 1fr;
                column-gap:1rem;
                align-items:center;
                padding:1rem 1.1rem;
                border-radius:.85rem;
                margin-bottom:.35rem;
                transition:background .18s ease, transform .18s ease;
            }
            .verif-row:last-child{ margin-bottom:0; }
            .verif-row:hover{ background:#f8fafc; }
            .verif-row.is-block{ align-items:flex-start; }
            .verif-row .ico{
                width:52px;height:52px;border-radius:.95rem;
                display:inline-flex;align-items:center;justify-content:center;
                background:linear-gradient(135deg,#eef2ff,#e0e7ff);
                color:#4f46e5;
                box-shadow:inset 0 0 0 1px rgba(79,70,229,.12);
                line-height:1;
                flex-shrink:0;
            }
            .verif-row .ico i{ font-size:1.5rem;line-height:1;display:block; }
            .verif-row.ico-success .ico{ background:linear-gradient(135deg,#dcfce7,#bbf7d0);color:#15803d;box-shadow:inset 0 0 0 1px rgba(21,128,61,.12); }
            .verif-row.ico-info    .ico{ background:linear-gradient(135deg,#cffafe,#a5f3fc);color:#0891b2;box-shadow:inset 0 0 0 1px rgba(8,145,178,.15); }
            .verif-row.ico-warning .ico{ background:linear-gradient(135deg,#fef3c7,#fde68a);color:#b45309;box-shadow:inset 0 0 0 1px rgba(180,83,9,.15); }
            .verif-row.ico-rose    .ico{ background:linear-gradient(135deg,#ffe4e6,#fecdd3);color:#be123c;box-shadow:inset 0 0 0 1px rgba(190,18,60,.12); }
            .verif-row .body{ min-width:0; }
            .verif-row .lbl{ font-size:.72rem;color:#64748b;text-transform:uppercase;letter-spacing:.08em;font-weight:600;margin-bottom:.2rem; }
            .verif-row .val{ font-weight:600;color:#0f172a;word-break:break-word;line-height:1.45;font-size:.97rem; }
            .verif-row .val.muted{ font-weight:500;color:#94a3b8;font-style:italic; }
            .verif-row + .verif-row{ border-top:1px solid #f1f3f7; }
            .verif-row:hover + .verif-row, .verif-row:hover{ border-top-color:transparent; }

            .ktp-frame{
                position:relative;border-radius:.85rem;overflow:hidden;
                background:linear-gradient(135deg,#f1f5f9,#e2e8f0);
                aspect-ratio:1.586/1;
                display:flex;align-items:center;justify-content:center;
                border:1px solid #e2e8f0;
            }
            .ktp-frame img{
                width:100%;height:100%;object-fit:cover;
                transition:transform .35s ease;
            }
            .ktp-frame:hover img{ transform:scale(1.04); }
            .ktp-frame .zoom{
                position:absolute;inset:auto .5rem .5rem auto;
                background:rgba(15,23,42,.7);color:#fff;
                width:36px;height:36px;border-radius:50%;
                display:inline-flex;align-items:center;justify-content:center;
                opacity:0;transition:opacity .2s;
            }
            .ktp-frame:hover .zoom{ opacity:1; }
            .ktp-empty{
                color:#94a3b8;font-size:.95rem;text-align:center;
            }

            .timeline-modern{ display:flex;align-items:center;gap:.25rem;flex-wrap:nowrap;overflow-x:auto; }
            .tl-step{ flex:1 1 0;min-width:120px;display:flex;flex-direction:column;align-items:center;text-align:center; }
            .tl-step .dot{
                width:34px;height:34px;border-radius:50%;
                display:inline-flex;align-items:center;justify-content:center;
                background:#e2e8f0;color:#94a3b8;border:3px solid #fff;
                box-shadow:0 0 0 1px #e2e8f0;
                font-size:.95rem;
            }
            .tl-step.done .dot{ background:#22c55e;color:#fff;box-shadow:0 0 0 1px #16a34a; }
            .tl-step.failed .dot{ background:#ef4444;color:#fff;box-shadow:0 0 0 1px #dc2626; }
            .tl-step.current .dot{ background:#6366f1;color:#fff;box-shadow:0 0 0 1px #4f46e5;animation:pulse 1.6s infinite; }
            @keyframes pulse{ 0%{box-shadow:0 0 0 0 rgba(99,102,241,.55)} 70%{box-shadow:0 0 0 10px rgba(99,102,241,0)} 100%{box-shadow:0 0 0 0 rgba(99,102,241,0)} }
            .tl-line{ flex:1 1 0;height:3px;background:#e2e8f0;border-radius:3px;margin-top:14px; }
            .tl-line.done{ background:linear-gradient(90deg,#22c55e,#16a34a); }
            .tl-step .lbl{ font-size:.78rem;font-weight:600;color:#0f172a;margin-top:.4rem; }
            .tl-step .sub{ font-size:.7rem;color:#94a3b8; }

            .action-btn-approve{
                background:linear-gradient(135deg,#10b981,#059669);
                color:#fff;border:none;
                box-shadow:0 8px 20px -10px rgba(16,185,129,.7);
            }
            .action-btn-approve:hover{ filter:brightness(1.06);color:#fff; }
            .action-btn-reject{
                border:1px solid #fecaca;background:#fff;color:#dc2626;
            }
            .action-btn-reject:hover{ background:#fee2e2;color:#b91c1c; }

            .copy-btn{
                background:transparent;border:none;color:#94a3b8;
                font-size:.85rem;padding:.15rem .35rem;border-radius:.4rem;
            }
            .copy-btn:hover{ background:#f1f5f9;color:#0f172a; }

            .biz-desc{
                white-space:pre-line;background:#fafbfc;border:1px dashed #e2e8f0;
                border-radius:.6rem;padding:.85rem 1rem;color:#334155;
                font-size:.92rem;line-height:1.6;
            }
        </style>
    @endpush

    <div class="mb-3">
        <a href="{{ route('admin.verifikasi.index') }}" class="btn btn-link text-secondary ps-0">
            <i class="ti ti-arrow-left me-1"></i> Kembali ke daftar
        </a>
    </div>

    {{-- HERO --}}
    <div class="verif-hero p-4 p-md-4 mb-3">
        <div class="d-flex flex-wrap align-items-center gap-3 position-relative">
            <div class="verif-avatar">{{ $initials ?: 'P' }}</div>
            <div class="flex-fill" style="min-width:240px">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h1 class="h2 mb-0">{{ $petani->name }}</h1>
                    <span class="verif-badge {{ $badgeClass }}">
                        <i class="ti ti-{{ $badgeIcon }}"></i> {{ $badgeLabel }}
                    </span>
                </div>
                <div class="text-secondary">
                    <i class="ti ti-building-store me-1"></i>
                    {{ $petani->nama_usaha ?: 'Belum mengisi nama usaha' }}
                </div>
                <div class="d-flex flex-wrap gap-3 mt-2 small text-secondary">
                    <span><i class="ti ti-mail me-1"></i>{{ $petani->email }}</span>
                    @if ($petani->no_hp)
                        <span><i class="ti ti-phone me-1"></i>{{ $petani->no_hp }}</span>
                    @endif
                    <span><i class="ti ti-calendar me-1"></i>Daftar {{ $petani->created_at->translatedFormat('d M Y') }}</span>
                </div>
            </div>
            <div class="text-end">
                <div class="text-secondary small">ID Petani</div>
                <div class="h3 mb-0 font-monospace">#{{ str_pad((string) $petani->id, 4, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        {{-- TIMELINE --}}
        <div class="mt-4">
            <div class="timeline-modern">
                @foreach ($steps as $i => $step)
                    @php
                        $isCurrent = $status === 'pending' && $i === 2;
                        $isFailed  = ! empty($step['failed']);
                        $cls = $step['done'] ? 'done' : ($isCurrent ? 'current' : '');
                        if ($isFailed) $cls = 'failed';
                    @endphp
                    <div class="tl-step {{ $cls }}">
                        <div class="dot">
                            @if ($isFailed)
                                <i class="ti ti-x"></i>
                            @elseif ($step['done'])
                                <i class="ti ti-check"></i>
                            @elseif ($isCurrent)
                                <i class="ti ti-loader-2"></i>
                            @else
                                {{ $i + 1 }}
                            @endif
                        </div>
                        <div class="lbl">{{ $step['label'] }}</div>
                        @if (! empty($step['date']))
                            <div class="sub">{{ \Illuminate\Support\Carbon::parse($step['date'])->translatedFormat('d M Y') }}</div>
                        @else
                            <div class="sub">&nbsp;</div>
                        @endif
                    </div>
                    @if (! $loop->last)
                        <div class="tl-line {{ ($steps[$i + 1]['done'] ?? false) ? 'done' : '' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    {{-- ALERT REJECTED --}}
    @if ($status === 'rejected' && $petani->verification_note)
        <div class="alert alert-danger d-flex gap-2 align-items-start">
            <i class="ti ti-info-circle mt-1"></i>
            <div>
                <div class="fw-semibold">Pengajuan sebelumnya ditolak</div>
                <div class="small">{{ $petani->verification_note }}</div>
            </div>
        </div>
    @endif

    <div class="row g-3">
        {{-- LEFT --}}
        <div class="col-lg-7">
            <div class="verif-card accent-primary mb-3">
                <div class="card-header">
                    <div class="header-ico"><i class="ti ti-user-circle"></i></div>
                    <div class="header-text">
                        <h3 class="h-title">Identitas Petani</h3>
                        <div class="h-sub">Data pribadi yang digunakan untuk verifikasi akun</div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="verif-list">
                        <div class="verif-row">
                            <div class="ico"><i class="ti ti-user"></i></div>
                            <div class="body">
                                <div class="lbl">Nama Lengkap</div>
                                <div class="val">{{ $petani->name }}</div>
                            </div>
                        </div>
                        <div class="verif-row ico-info">
                            <div class="ico"><i class="ti ti-mail"></i></div>
                            <div class="body">
                                <div class="lbl">Email</div>
                                <div class="val d-flex align-items-center gap-1">
                                    <span>{{ $petani->email }}</span>
                                    <button type="button" class="copy-btn" data-copy="{{ $petani->email }}" title="Salin">
                                        <i class="ti ti-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="verif-row ico-success">
                            <div class="ico"><i class="ti ti-phone"></i></div>
                            <div class="body">
                                <div class="lbl">No. HP</div>
                                <div class="val {{ $petani->no_hp ? '' : 'muted' }}">{{ $petani->no_hp ?? 'Belum diisi' }}</div>
                            </div>
                        </div>
                        <div class="verif-row ico-warning">
                            <div class="ico"><i class="ti ti-id-badge-2"></i></div>
                            <div class="body">
                                <div class="lbl">NIK</div>
                                <div class="val font-monospace {{ $petani->nik ? '' : 'muted' }}">
                                    {{ $petani->nik ?? 'Belum diisi' }}
                                    @if ($petani->nik)
                                        <button type="button" class="copy-btn ms-1" data-copy="{{ $petani->nik }}" title="Salin">
                                            <i class="ti ti-copy"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="verif-card accent-success mb-3">
                <div class="card-header">
                    <div class="header-ico"><i class="ti ti-building-store"></i></div>
                    <div class="header-text">
                        <h3 class="h-title">Profil Usaha Tani</h3>
                        <div class="h-sub">Informasi toko & lokasi pengiriman hasil panen</div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="verif-list">
                        <div class="verif-row ico-success">
                            <div class="ico"><i class="ti ti-tag"></i></div>
                            <div class="body">
                                <div class="lbl">Nama Usaha</div>
                                <div class="val {{ $petani->nama_usaha ? '' : 'muted' }}">{{ $petani->nama_usaha ?? 'Belum diisi' }}</div>
                            </div>
                        </div>
                        <div class="verif-row ico-rose">
                            <div class="ico"><i class="ti ti-map-pin"></i></div>
                            <div class="body">
                                <div class="lbl">Alamat</div>
                                <div class="val {{ $petani->alamat ? '' : 'muted' }}">
                                    @if ($petani->hasCompleteAddress())
                                        <div>{{ $petani->alamat }}</div>
                                        <div class="small text-secondary mt-1 fw-normal">
                                            {{ $petani->district_name }}, {{ $petani->city_name }}, {{ $petani->province_name }}
                                        </div>
                                    @else
                                        {{ $petani->alamat ?? 'Belum diisi' }}
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="verif-row is-block ico-info">
                            <div class="ico"><i class="ti ti-align-left"></i></div>
                            <div class="body w-100">
                                <div class="lbl mb-1">Deskripsi Usaha</div>
                                @if ($petani->deskripsi_usaha)
                                    <div class="biz-desc">{{ $petani->deskripsi_usaha }}</div>
                                @else
                                    <div class="val muted">Belum diisi</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT --}}
        <div class="col-lg-5">
            <div class="verif-card accent-info mb-3">
                <div class="card-header">
                    <div class="header-ico"><i class="ti ti-photo-scan"></i></div>
                    <div class="header-text">
                        <h3 class="h-title">Dokumen KTP</h3>
                        <div class="h-sub">Bukti identitas yang diunggah petani</div>
                    </div>
                    @if ($petani->ktp_image_url)
                        <div class="header-action">
                            <a href="{{ $petani->ktp_image_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="ti ti-external-link me-1"></i> Buka tab baru
                            </a>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    @if ($petani->ktp_image_url)
                        <a href="#" data-bs-toggle="modal" data-bs-target="#ktp-modal" class="d-block">
                            <div class="ktp-frame">
                                <img src="{{ $petani->ktp_image_url }}" alt="Foto KTP">
                                <span class="zoom"><i class="ti ti-zoom-in"></i></span>
                            </div>
                        </a>
                        <div class="text-center small text-secondary mt-2">
                            Klik gambar untuk perbesar
                        </div>
                    @else
                        <div class="ktp-frame">
                            <div class="ktp-empty">
                                <i class="ti ti-photo-off d-block mb-2" style="font-size:2rem"></i>
                                KTP belum diunggah
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($status === 'pending')
                <div class="verif-card accent-warning" style="border-color:#fde68a">
                    <div class="card-header">
                        <div class="header-ico"><i class="ti ti-gavel"></i></div>
                        <div class="header-text">
                            <h3 class="h-title">Tindakan Verifikasi</h3>
                            <div class="h-sub">Setujui atau tolak pengajuan ini</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="small text-secondary mb-3">
                            Pastikan data identitas, profil usaha, dan foto KTP sesuai sebelum
                            menyetujui atau menolak pengajuan ini.
                        </p>

                        <button type="button" class="btn action-btn-approve w-100 py-2 mb-3"
                                data-bs-toggle="modal" data-bs-target="#approve-modal">
                            <i class="ti ti-circle-check me-1"></i> Setujui Verifikasi
                        </button>

                        <div class="text-center text-secondary small mb-2">— atau —</div>

                        <button type="button" class="btn action-btn-reject w-100 py-2"
                                data-bs-toggle="modal" data-bs-target="#reject-modal">
                            <i class="ti ti-circle-x me-1"></i> Tolak Pengajuan
                        </button>
                    </div>
                </div>
            @elseif ($status === 'verified')
                <div class="verif-card">
                    <div class="card-body text-center py-4">
                        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3"
                             style="width:64px;height:64px;background:linear-gradient(135deg,#10b981,#059669);color:#fff">
                            <i class="ti ti-shield-check" style="font-size:1.8rem"></i>
                        </div>
                        <div class="h4 mb-1">Petani Terverifikasi</div>
                        <div class="text-secondary small">
                            Akun ini sudah memiliki izin penuh untuk berjualan di marketplace.
                        </div>
                    </div>
                </div>
            @elseif ($status === 'not_submitted')
                <div class="verif-card">
                    <div class="card-body text-center py-4">
                        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3"
                             style="width:64px;height:64px;background:#fef3c7;color:#b45309">
                            <i class="ti ti-alert-triangle" style="font-size:1.8rem"></i>
                        </div>
                        <div class="h4 mb-1">Belum Mengajukan</div>
                        <div class="text-secondary small">
                            Petani belum mengirim dokumen verifikasi. Belum ada tindakan yang dapat dilakukan.
                        </div>
                    </div>
                </div>
            @elseif ($status === 'rejected')
                <div class="verif-card">
                    <div class="card-body text-center py-4">
                        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3"
                             style="width:64px;height:64px;background:#fee2e2;color:#b91c1c">
                            <i class="ti ti-circle-x" style="font-size:1.8rem"></i>
                        </div>
                        <div class="h4 mb-1">Pengajuan Ditolak</div>
                        <div class="text-secondary small">
                            Petani perlu memperbaiki data lalu mengajukan ulang verifikasi.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL PREVIEW KTP --}}
    @if ($petani->ktp_image_url)
        <div class="modal fade" id="ktp-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content" style="background:#0f172a">
                    <div class="modal-header border-0">
                        <h5 class="modal-title text-white">Foto KTP — {{ $petani->name }}</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <img src="{{ $petani->ktp_image_url }}" alt="KTP {{ $petani->name }}"
                             style="max-width:100%;max-height:75vh;border-radius:.5rem">
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL APPROVE --}}
    @if ($status === 'pending')
        <div class="modal fade" id="approve-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('admin.verifikasi.approve', $petani) }}" method="POST" class="modal-content border-0 shadow-lg">
                    @csrf
                    <div class="modal-body p-4 text-center">
                        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-3"
                             style="width:72px;height:72px;background:linear-gradient(135deg,#10b981,#059669);color:#fff">
                            <i class="ti ti-shield-check" style="font-size:2rem"></i>
                        </div>
                        <h3 class="mb-1">Setujui Verifikasi?</h3>
                        <p class="text-secondary mb-0">
                            Setelah disetujui, <strong>{{ $petani->name }}</strong> akan langsung dapat
                            mempublikasikan produk dan menerima pesanan di marketplace.
                        </p>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-link link-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn action-btn-approve flex-fill" id="approve-confirm-btn">
                            <i class="ti ti-circle-check me-1"></i> Ya, Setujui
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL REJECT --}}
        <div class="modal fade" id="reject-modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('admin.verifikasi.reject', $petani) }}" method="POST" class="modal-content border-0 shadow-lg" id="reject-form">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="text-center mb-3">
                            <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center mb-2"
                                 style="width:64px;height:64px;background:#fee2e2;color:#b91c1c">
                                <i class="ti ti-circle-x" style="font-size:1.8rem"></i>
                            </div>
                            <h3 class="mb-1">Tolak Pengajuan?</h3>
                            <p class="text-secondary small mb-0">
                                Catatan akan dikirim ke <strong>{{ $petani->name }}</strong> agar bisa
                                memperbaiki dokumen dan mengajukan ulang.
                            </p>
                        </div>
                        <label class="form-label required">Alasan Penolakan</label>
                        <textarea name="verification_note" rows="4"
                                  class="form-control @error('verification_note') is-invalid @enderror"
                                  placeholder="Mis. Foto KTP buram, NIK tidak sesuai, nama usaha kosong..."
                                  required>{{ old('verification_note') }}</textarea>
                        @error('verification_note')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-link link-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger flex-fill" id="reject-confirm-btn">
                            <i class="ti ti-circle-x me-1"></i> Ya, Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- TOAST CONTAINER (untuk feedback aksi non-blocking, mis. salin teks) --}}
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1080">
        <div id="app-toast" class="toast align-items-center border-0 shadow-lg" role="status" aria-live="polite" aria-atomic="true" data-bs-delay="2200">
            <div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i id="app-toast-icon" class="ti ti-check text-success" style="font-size:1.25rem"></i>
                    <span id="app-toast-msg">Tersalin</span>
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Helper toast — dipakai untuk feedback ringan (salin teks).
            const toastEl  = document.getElementById('app-toast');
            const toastMsg = document.getElementById('app-toast-msg');
            const toastIco = document.getElementById('app-toast-icon');
            const toast    = toastEl ? new bootstrap.Toast(toastEl) : null;
            function showToast(msg, type) {
                if (!toast) return;
                toastMsg.textContent = msg;
                toastIco.className = 'ti ' + (type === 'error' ? 'ti-alert-circle text-danger' : 'ti-check text-success');
                toast.show();
            }

            // Salin teks (email / NIK).
            document.querySelectorAll('[data-copy]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    try {
                        await navigator.clipboard.writeText(btn.dataset.copy);
                        const icon = btn.querySelector('i');
                        const old = icon.className;
                        icon.className = 'ti ti-check';
                        btn.style.color = '#16a34a';
                        setTimeout(() => { icon.className = old; btn.style.color = ''; }, 1100);
                        showToast('Disalin ke clipboard');
                    } catch (_) {
                        showToast('Gagal menyalin', 'error');
                    }
                });
            });

            // Spinner saat submit modal approve/reject — biar tombol tidak bisa diklik 2x.
            ['approve-confirm-btn', 'reject-confirm-btn'].forEach(id => {
                const btn = document.getElementById(id);
                if (!btn) return;
                btn.closest('form')?.addEventListener('submit', () => {
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
                });
            });
        </script>
    @endpush
</x-layouts.app>
