<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\KategoriController as AdminKategoriController;
use App\Http\Controllers\Admin\PenggunaController as AdminPenggunaController;
use App\Http\Controllers\Admin\ProdukController as AdminProdukController;
use App\Http\Controllers\Admin\VerifikasiController as AdminVerifikasiController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\Pelanggan\PesananController as PelangganPesananController;
use App\Http\Controllers\Petani\ProdukController as PetaniProdukController;
use App\Http\Controllers\Petani\PesananController as PetaniPesananController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [KatalogController::class, 'index'])->name('home');

Route::prefix('katalog')->name('pelanggan.katalog.')->group(function () {
    Route::get('/',              [KatalogController::class, 'index'])->name('index');
    Route::get('/{produk:slug}', [KatalogController::class, 'show'])->name('show');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->role === UserRole::Pelanggan) {
            return redirect()->route('pelanggan.katalog.index');
        }
        return view('pages.dashboard');
    })->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',    [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/',  [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('role:petani')->prefix('petani')->name('petani.')->group(function () {
        Route::prefix('produk')->name('produk.')->group(function () {
            Route::get('/',       [PetaniProdukController::class, 'index'])->name('index');
            Route::get('/create', [PetaniProdukController::class, 'create'])->name('create');
        });
        Route::get('/pesanan', [PetaniPesananController::class, 'index'])->name('pesanan.index');
        Route::view('/laporan', 'pages.petani.laporan.index')->name('laporan.index');
    });

    Route::middleware('role:pelanggan')->prefix('pelanggan')->name('pelanggan.')->group(function () {
        Route::post('/keranjang',  [CartController::class, 'store'])->name('keranjang.store');
        Route::view('/keranjang',  'pages.pelanggan.keranjang.index')->name('keranjang.index');
        Route::view('/checkout',   'pages.pelanggan.checkout.index')->name('checkout.index');
        Route::view('/pembayaran', 'pages.pelanggan.pembayaran.index')->name('pembayaran.index');
        Route::get('/pesanan',     [PelangganPesananController::class, 'index'])->name('pesanan.index');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/pengguna',          [AdminPenggunaController::class, 'index'])->name('pengguna.index');
        Route::get('/verifikasi-petani', [AdminVerifikasiController::class, 'index'])->name('verifikasi.index');
        Route::get('/produk',            [AdminProdukController::class, 'index'])->name('produk.index');
        Route::get('/kategori',          [AdminKategoriController::class, 'index'])->name('kategori.index');

        Route::prefix('hero')->name('hero.')->group(function () {
            Route::get('/',                  [HeroSlideController::class, 'index'])->name('index');
            Route::get('/create',            [HeroSlideController::class, 'create'])->name('create');
            Route::post('/',                 [HeroSlideController::class, 'store'])->name('store');
            Route::get('/{slide}/edit',      [HeroSlideController::class, 'edit'])->name('edit');
            Route::put('/{slide}',           [HeroSlideController::class, 'update'])->name('update');
            Route::delete('/{slide}',        [HeroSlideController::class, 'destroy'])->name('destroy');
            Route::patch('/{slide}/toggle',  [HeroSlideController::class, 'toggle'])->name('toggle');
        });
    });
});

require __DIR__.'/auth.php';
