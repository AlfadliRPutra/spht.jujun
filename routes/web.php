<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\KategoriController as AdminKategoriController;
use App\Http\Controllers\Admin\PenggunaController as AdminPenggunaController;
use App\Http\Controllers\Admin\TokoController as AdminTokoController;
use App\Http\Controllers\Admin\VerifikasiController as AdminVerifikasiController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\KatalogController;
use App\Http\Controllers\Pelanggan\PembayaranController as PelangganPembayaranController;
use App\Http\Controllers\Pelanggan\PesananController as PelangganPesananController;
use App\Http\Controllers\Petani\LaporanController as PetaniLaporanController;
use App\Http\Controllers\Petani\ProdukController as PetaniProdukController;
use App\Http\Controllers\Petani\PesananController as PetaniPesananController;
use App\Http\Controllers\Petani\VerifikasiController as PetaniVerifikasiController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [KatalogController::class, 'index'])->name('home');

Route::prefix('katalog')->name('pelanggan.katalog.')->group(function () {
    Route::get('/',              [KatalogController::class, 'index'])->name('index');
    Route::get('/{produk:slug}', [KatalogController::class, 'show'])->name('show');
});

Route::post('/midtrans/notification', [PelangganPembayaranController::class, 'notification'])
    ->name('midtrans.notification');

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
            Route::get('/',                 [PetaniProdukController::class, 'index'])->name('index');
            Route::get('/create',           [PetaniProdukController::class, 'create'])->name('create');
            Route::put('/{produk:slug}',    [PetaniProdukController::class, 'update'])->name('update');
            Route::delete('/{produk:slug}', [PetaniProdukController::class, 'destroy'])->name('destroy');
            Route::post('/',      [PetaniProdukController::class, 'store'])->name('store');
        });
        Route::prefix('pesanan')->name('pesanan.')->group(function () {
            Route::get('/',                     [PetaniPesananController::class, 'index'])->name('index');
            Route::post('/{order}/ship',        [PetaniPesananController::class, 'ship'])->name('ship');
            Route::post('/{order}/complete',    [PetaniPesananController::class, 'complete'])->name('complete');
            Route::post('/{order}/cancel',      [PetaniPesananController::class, 'cancel'])->name('cancel');
        });
        Route::get('/laporan', [PetaniLaporanController::class, 'index'])->name('laporan.index');

        Route::prefix('verifikasi')->name('verifikasi.')->group(function () {
            Route::get('/',         [PetaniVerifikasiController::class, 'index'])->name('index');
            Route::post('/',        [PetaniVerifikasiController::class, 'store'])->name('store');
            Route::post('/dismiss', [PetaniVerifikasiController::class, 'dismiss'])->name('dismiss');
        });
    });

    Route::middleware('role:pelanggan')->prefix('pelanggan')->name('pelanggan.')->group(function () {
        Route::post('/keranjang',  [CartController::class, 'store'])->name('keranjang.store');
        Route::view('/keranjang',  'pages.pelanggan.keranjang.index')->name('keranjang.index');
        Route::view('/checkout',   'pages.pelanggan.checkout.index')->name('checkout.index');

        Route::post('/pembayaran',                    [PelangganPembayaranController::class, 'store'])->name('pembayaran.store');
        Route::get('/pembayaran',                     [PelangganPembayaranController::class, 'latest'])->name('pembayaran.latest');
        Route::get('/pembayaran/{order}',             [PelangganPembayaranController::class, 'show'])->name('pembayaran.show');
        Route::get('/pembayaran/{order}/finish',      [PelangganPembayaranController::class, 'finish'])->name('pembayaran.finish');
        Route::get('/pembayaran/{order}/unfinish',    [PelangganPembayaranController::class, 'unfinish'])->name('pembayaran.unfinish');
        Route::get('/pembayaran/{order}/error',       [PelangganPembayaranController::class, 'error'])->name('pembayaran.error');
        Route::post('/pembayaran/{order}/sync',       [PelangganPembayaranController::class, 'sync'])->name('pembayaran.sync');

        Route::get('/pesanan',     [PelangganPesananController::class, 'index'])->name('pesanan.index');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::prefix('pengguna')->name('pengguna.')->group(function () {
            Route::get('/',              [AdminPenggunaController::class, 'index'])->name('index');
            Route::post('/',             [AdminPenggunaController::class, 'store'])->name('store');
            Route::put('/{pengguna}',    [AdminPenggunaController::class, 'update'])->name('update');
            Route::delete('/{pengguna}', [AdminPenggunaController::class, 'destroy'])->name('destroy');
        });
        Route::prefix('verifikasi-petani')->name('verifikasi.')->group(function () {
            Route::get('/',                 [AdminVerifikasiController::class, 'index'])->name('index');
            Route::get('/{petani}',         [AdminVerifikasiController::class, 'show'])->name('show');
            Route::post('/{petani}/approve',[AdminVerifikasiController::class, 'approve'])->name('approve');
            Route::post('/{petani}/reject', [AdminVerifikasiController::class, 'reject'])->name('reject');
        });
        Route::prefix('toko')->name('toko.')->group(function () {
            Route::get('/',                                  [AdminTokoController::class, 'index'])->name('index');
            Route::get('/{petani}',                          [AdminTokoController::class, 'show'])->name('show');
            Route::patch('/{petani}/produk/{product}/toggle',[AdminTokoController::class, 'toggleProduct'])->name('product_toggle');
        });
        Route::prefix('kategori')->name('kategori.')->group(function () {
            Route::get('/',              [AdminKategoriController::class, 'index'])->name('index');
            Route::post('/',             [AdminKategoriController::class, 'store'])->name('store');
            Route::put('/{kategori}',    [AdminKategoriController::class, 'update'])->name('update');
            Route::delete('/{kategori}', [AdminKategoriController::class, 'destroy'])->name('destroy');
        });

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
