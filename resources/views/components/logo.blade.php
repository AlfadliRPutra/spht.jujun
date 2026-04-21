@props([
    'href' => url('/'),
    'class' => '',
    'width' => 110,
    'height' => 32,
])

<a href="{{ $href }}" class="navbar-brand navbar-brand-autodark {{ $class }}" aria-label="{{ config('app.name') }}">
    <img src="{{ asset('img/logo.svg') }}" width="{{ $width }}" height="{{ $height }}" alt="{{ config('app.name') }}" class="navbar-brand-image">
</a>
