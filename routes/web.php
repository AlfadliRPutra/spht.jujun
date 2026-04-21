<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', fn () => view('pages.dashboard'))->name('dashboard');

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/',    [ProfileController::class, 'edit'])->name('edit');
        Route::patch('/',  [ProfileController::class, 'update'])->name('update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('role:petani')->prefix('petani')->name('petani.')->group(function () {
        Route::prefix('produk')->name('produk.')->group(function () {
            Route::view('/',       'pages.petani.produk.index')->name('index');
            Route::view('/create', 'pages.petani.produk.form')->name('create');
        });
        Route::view('/pesanan', 'pages.petani.pesanan.index')->name('pesanan.index');
        Route::view('/laporan', 'pages.petani.laporan.index')->name('laporan.index');
    });

    Route::middleware('role:pelanggan')->prefix('pelanggan')->name('pelanggan.')->group(function () {
        Route::prefix('katalog')->name('katalog.')->group(function () {
            Route::view('/',  'pages.pelanggan.katalog.index')->name('index');
            Route::view('/1', 'pages.pelanggan.katalog.show')->name('show');
        });
        Route::view('/keranjang',  'pages.pelanggan.keranjang.index')->name('keranjang.index');
        Route::view('/checkout',   'pages.pelanggan.checkout.index')->name('checkout.index');
        Route::view('/pembayaran', 'pages.pelanggan.pembayaran.index')->name('pembayaran.index');
        Route::view('/pesanan',    'pages.pelanggan.pesanan.index')->name('pesanan.index');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::view('/pengguna',          'pages.admin.pengguna.index')->name('pengguna.index');
        Route::view('/verifikasi-petani', 'pages.admin.verifikasi.index')->name('verifikasi.index');
        Route::view('/produk',            'pages.admin.produk.index')->name('produk.index');
        Route::view('/kategori',          'pages.admin.kategori.index')->name('kategori.index');
    });
});

require __DIR__.'/auth.php';
