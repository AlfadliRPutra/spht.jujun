{{--
    Tampilkan flash message & validation summary (bag default) sebagai
    SweetAlert popup. Disertakan oleh setiap layout (app, storefront, guest)
    sehingga UX notifikasi konsisten di seluruh aplikasi.

    Form yang memakai bag custom (mis. updatePassword) tetap menampilkan
    error inline lewat <x-form-errors bag="..."> karena bag non-default
    tidak ditangkap di sini.
--}}
@php
    $flashSuccess = session('success');
    $flashError   = session('error');
    $flashWarning = session('warning');
    $flashInfo    = session('info');

    $validationList = [];
    if (isset($errors) && $errors->getBag('default')->any()) {
        $validationList = $errors->getBag('default')->all();
    }

    $validationHtml = '';
    if (! empty($validationList)) {
        $validationHtml = '<ul style="text-align:left;padding-left:1.25rem;margin:0">';
        foreach ($validationList as $msg) {
            $validationHtml .= '<li>' . e($msg) . '</li>';
        }
        $validationHtml .= '</ul>';
    }
@endphp

@if ($flashSuccess || $flashError || $flashWarning || $flashInfo || $validationHtml !== '')
    <script>
        (function () {
            function showFlash() {
                if (typeof Swal === 'undefined') return;

                @if ($flashSuccess)
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: @json($flashSuccess),
                        confirmButtonColor: '#16a34a',
                        timer: 2500,
                        timerProgressBar: true,
                        showConfirmButton: false,
                    });
                @elseif ($flashError)
                    Swal.fire({
                        icon: 'error',
                        title: 'Terjadi kesalahan',
                        text: @json($flashError),
                        confirmButtonColor: '#dc2626',
                    });
                @elseif ($flashWarning)
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: @json($flashWarning),
                        confirmButtonColor: '#f59e0b',
                    });
                @elseif ($flashInfo)
                    Swal.fire({
                        icon: 'info',
                        title: 'Informasi',
                        text: @json($flashInfo),
                        confirmButtonColor: '#0ea5e9',
                    });
                @elseif ($validationHtml !== '')
                    Swal.fire({
                        icon: 'error',
                        title: 'Periksa kembali isian Anda',
                        html: @json($validationHtml),
                        confirmButtonColor: '#dc2626',
                    });
                @endif
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showFlash);
            } else {
                showFlash();
            }
        })();
    </script>
@endif
