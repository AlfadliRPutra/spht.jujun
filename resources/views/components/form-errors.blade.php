@props([
    'bag'     => 'default',
    'title'   => 'Periksa kembali isian Anda',
    'success' => 'success',
])

@php
    $errorList  = isset($errors) ? $errors->getBag($bag) : new \Illuminate\Support\MessageBag();
    $successKey = $success === false || $success === null ? null : (string) $success;
@endphp

@if ($successKey && session()->has($successKey))
    <div class="alert alert-success alert-dismissible mb-3" role="alert">
        <div class="d-flex align-items-start gap-2">
            <i class="ti ti-circle-check mt-1"></i>
            <div class="small">{{ session($successKey) }}</div>
        </div>
        <a class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></a>
    </div>
@endif

@if ($errorList->any())
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
