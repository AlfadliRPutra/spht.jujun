@php
    use App\Enums\OrderStatus;
    use App\Enums\UserRole;
    use App\Models\Category;
    use App\Models\OrderItem;
    use App\Models\Product;
    use App\Models\User;

    $title  = 'Dashboard';
    $active = 'dashboard';
    $user   = auth()->user();
@endphp

<x-layouts.app :title="$title" :active="$active">
    @switch($user->role)
        @case(UserRole::Petani)
            @include('pages.partials.dashboard-petani', ['user' => $user])
            @break

        @case(UserRole::Admin)
            @include('pages.partials.dashboard-admin', ['user' => $user])
            @break
    @endswitch
</x-layouts.app>
