@props([
    'href' => url('/'),
    'class' => '',
    'width' => 40,
    'height' => 40,
    'withText' => false,
])

<a href="{{ $href }}" class="navbar-brand d-flex align-items-center {{ $class }}" aria-label="{{ config('app.name') }}">
    <img src="{{ asset('img/logo.png') }}" width="{{ $width }}" height="{{ $height }}" alt="{{ config('app.name') }}" class="navbar-brand-image" style="object-fit: contain; filter: none;">
    @if ($withText)
        <span class="ms-2">SPHT-JUJUN</span>
    @endif
</a>
