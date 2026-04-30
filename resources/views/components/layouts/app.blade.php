@props(['title' => null, 'active' => null])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title.' — '.config('app.name') : config('app.name') }}</title>

    <link rel="icon" href="{{ asset('img/favicon.ico') }}" type="image/x-icon">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.41.1/dist/tabler-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    @include('partials.theme')

    <style>
        /* Flash alert wrapper */
        .flash-wrap{ position:sticky;top:0;z-index:1020; }
        .flash-wrap .alert{
            margin:.75rem 0;border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="page">
        <x-sidebar :active="$active ?? null" />

        <div class="page-wrapper">
            <x-header :title="$title ?? null" />

            <div class="page-body">
                <div class="container-xl">
                    <div class="flash-wrap">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible d-flex align-items-center gap-2" role="alert">
                                <i class="ti ti-circle-check"></i><span>{{ session('success') }}</span>
                                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible d-flex align-items-center gap-2" role="alert">
                                <i class="ti ti-alert-circle"></i><span>{{ session('error') }}</span>
                                <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                            </div>
                        @endif
                    </div>

                    {{ $slot }}
                </div>
            </div>

            <x-footer />
        </div>
    </div>

    @guest
        <x-register-role-modal />
    @endguest

    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        // Pindahkan semua .modal jadi anak langsung <body> sebelum Bootstrap
        // mem-bind handler. Tanpa ini, modal kadang gagal close karena terjebak
        // di stacking context parent (sticky sidebar/header, flex container, dll).
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.modal').forEach(function (m) {
                if (m.parentElement !== document.body) {
                    document.body.appendChild(m);
                }
            });

            // Konfirmasi aksi via SweetAlert. Pakai pada <form data-confirm="...">.
            // Atribut tambahan opsional:
            //   data-confirm-title, data-confirm-icon (warning|question|success|info|error),
            //   data-confirm-button (label tombol konfirmasi),
            //   data-confirm-color  (warna tombol konfirmasi).
            document.body.addEventListener('submit', function (e) {
                const form = e.target;
                if (! (form instanceof HTMLFormElement)) return;
                if (! form.hasAttribute('data-confirm')) return;
                if (form.dataset.confirmed === '1') return; // sudah dikonfirmasi → biarkan submit

                e.preventDefault();
                Swal.fire({
                    title: form.dataset.confirmTitle || 'Konfirmasi',
                    text:  form.dataset.confirm,
                    icon:  form.dataset.confirmIcon  || 'question',
                    showCancelButton:   true,
                    confirmButtonText:  form.dataset.confirmButton || 'Ya, lanjutkan',
                    cancelButtonText:   'Batal',
                    confirmButtonColor: form.dataset.confirmColor  || '#16a34a',
                    cancelButtonColor:  '#64748b',
                    reverseButtons:     true,
                }).then((result) => {
                    if (! result.isConfirmed) return;
                    form.dataset.confirmed = '1';
                    form.submit();
                });
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
