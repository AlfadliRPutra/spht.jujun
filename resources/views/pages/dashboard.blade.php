@php($title = 'Dashboard')
@php($active = 'dashboard')

<x-layouts.app :title="$title" :active="$active">
    <div class="row row-cards">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Selamat datang</h3>
                    <p class="text-secondary">Ini adalah halaman dashboard menggunakan template Tabler.</p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
