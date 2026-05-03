@props([
    'bag'     => 'default',
    'title'   => 'Periksa kembali isian Anda',
    'success' => 'success',
])

@php
    $errorList = isset($errors) ? $errors->getBag($bag) : new \Illuminate\Support\MessageBag();
@endphp

{{-- Flash success & validation errors untuk bag default sudah ditangani oleh
     <partials.flash-popup> di setiap layout sebagai SweetAlert popup. Komponen
     ini hanya menampilkan summary inline untuk bag non-default (mis. bag
     "updatePassword" pada form profil) supaya tetap kontekstual di dekat form. --}}
@if ($bag !== 'default' && $errorList->any())
    <div class="alert alert-danger alert-dismissible mb-3" role="alert">
        <div class="d-flex gap-2">
            <i class="ti ti-alert-triangle mt-1"></i>
            <div class="flex-fill">
                <div class="fw-semibold mb-1">{{ $title }}</div>
                <ul class="mb-0 ps-3 small">
                    @foreach ($errorList->all() as $msg)
                        <li>{{ $msg }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></a>
    </div>
@endif
