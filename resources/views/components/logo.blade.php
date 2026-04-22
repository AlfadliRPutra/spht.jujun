@props([
    'href' => url('/'),
    'class' => '',
    'width' => 40,
    'height' => 40,
])

<a href="{{ $href }}" class="navbar-brand navbar-brand-autodark {{ $class }}" aria-label="{{ config('app.name') }}">
    <img src="{{ asset('img/logo.png') }}" width="{{ $width }}" height="{{ $height }}" alt="{{ config('app.name') }}" class="navbar-brand-image" style="object-fit: contain;">
</a>
