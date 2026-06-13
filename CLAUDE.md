# CLAUDE.md — Panduan Pengembangan SPHT Jujun

Dokumen ini adalah **kontrak konvensi** untuk semua developer (dan AI assistant) yang
bekerja di repo ini. Tujuannya satu: **logika & gaya setiap orang konsisten, tidak melenceng.**
Baca bagian yang relevan **sebelum** menulis kode. Kalau ragu, ikuti pola yang sudah ada —
jangan memperkenalkan pola baru tanpa alasan kuat.

---

## 1. Ringkasan Project

E-commerce hasil tani: **petani** menjual produk langsung ke **pelanggan**, dengan **admin**
sebagai pengelola master data & verifikasi. Marketplace multi-toko (satu keranjang bisa berisi
produk dari beberapa petani sekaligus), dengan ongkir per-toko (RajaOngkir/Komerce) dan
pembayaran via Midtrans Snap atau ambil-di-toko (pickup).

### Tech Stack (jangan ganti tanpa diskusi)
- **Laravel 12**, **PHP 8.2+**
- **DB**: **MySQL** (`.env` asli → `DB_CONNECTION=mysql`, database `spht_jujun`). Catatan:
  `.env.example` dan default `config/database.php` masih `sqlite` (bawaan Laravel), tapi project
  ini **dijalankan di MySQL** — set `DB_CONNECTION=mysql` saat setup. Schema string length default
  191 (lihat `AppServiceProvider`, supaya index utf8mb4 muat di MySQL lama).
- **Frontend**: Blade + **Tabler UI** (CSS via CDN, bukan Vite build) + **SweetAlert2**.
  Vite/`resources/js` ada tapi minimal — UI utama pakai komponen Blade + CDN.
- **PDF**: `barryvdh/laravel-dompdf` (invoice & resi).
- **Pembayaran**: `midtrans/midtrans-php` (Snap).
- **Ongkir**: RajaOngkir Komerce (HTTP client custom, bukan package).
- **Auth scaffolding**: Laravel Breeze (Blade).

---

## 2. Bahasa & Penamaan (PENTING — sumber inkonsistensi utama)

Project ini **bilingual by convention**. Patuhi pembagian ini:

| Konteks | Bahasa | Contoh |
|---|---|---|
| Domain / route / controller folder / view folder | **Indonesia** | `Pelanggan`, `Petani`, `pesanan`, `keranjang`, `pembayaran`, `wilayah` |
| Kolom DB domain | **Indonesia** | `harga`, `stok`, `nama`, `jumlah`, `alamat`, `nama_penerima`, `metode_pembayaran` |
| Model, relasi Eloquent, tabel infra | **Inggris** | `Product`, `Order`, `OrderItem`, `Address`, `province_id`, `is_active`, `created_at` |
| Komentar kode & pesan flash/validasi ke user | **Indonesia** | `->with('success', 'Produk berhasil ditambahkan.')` |
| Nama method, variabel teknis | **Inggris** | `calculateShipping()`, `addressSnapshot()`, `hasCompleteProfile()` |

**Aturan praktis:** kalau menyentuh konsep yang sudah ada (produk, pesanan, ongkir), pakai
istilah Indonesia yang sudah dipakai. Jangan campur — JANGAN bikin `Order::price` baru di
samping `harga`, atau route `petani.order.*` di samping `petani.pesanan.*`.

Role canonical (string value enum): `petani`, `pelanggan`, `admin`. Selalu lewat
`App\Enums\UserRole`, jangan hardcode string role di luar enum.

---

## 3. Struktur Direktori & Tanggung Jawab Layer

```
app/
├── Console/Commands/      Artisan command (mis. orders:expire-pending)
├── Enums/                 OrderStatus, PaymentMethod, UserRole — SATU sumber kebenaran status
├── Http/
│   ├── Controllers/
│   │   ├── Admin/         Controller untuk role admin
│   │   ├── Auth/          Breeze (jangan diubah kecuali perlu)
│   │   ├── Pelanggan/     Controller role pelanggan
│   │   ├── Petani/        Controller role petani
│   │   └── (root)         Controller publik: Katalog, Cart, Wilayah, Profile
│   ├── Middleware/        EnsureUserHasRole (alias 'role'), EnsureProfileComplete ('profile.complete')
│   └── Requests/          FormRequest untuk validasi yang dipakai ulang
├── Models/                Eloquent models
├── Notifications/         Email (verifikasi, reset password)
├── Providers/             AppServiceProvider (locale ID, password rules, paginator)
├── Services/              LOGIKA BISNIS berat → ShippingService, CheckoutSummaryService, RajaOngkirClient
├── Support/               Helper stateless → PublicUpload, Wilayah, WeightFormatter, EmailVerificationCode
└── View/Components/       Class komponen Blade
```

**Aturan layering (jangan dilanggar):**
- **Controller = orkestrasi tipis.** Validasi input → panggil Service/Model → redirect/view.
  Logika bisnis kompleks (hitung ongkir, grouping keranjang per toko, panggil API eksternal)
  **WAJIB di Service**, bukan di controller.
- **Service = logika domain murni**, idealnya bisa di-unit-test tanpa HTTP (lihat
  `ShippingServiceTest`). Service menerima data/Model, mengembalikan array/DTO terstruktur.
- **Support = helper stateless** (static methods), boleh menyentuh Model tapi tanpa state bisnis.
- **Model = data + aturan yang melekat pada entitas** (scope, accessor, transisi status sederhana
  seperti `Order::expireIfDue()`). Jangan taruh panggilan HTTP eksternal di Model.

Kalau butuh logika baru yang dipakai >1 controller → buat/extend **Service**, jangan copy-paste.

---

## 4. Domain Model & Aturan Bisnis (INTI — wajib paham sebelum ubah alur)

### 4.1 Peran (roles)
- **Petani**: punya `products`, kelola pesanan masuk, profil toko (`nama_usaha`, alamat toko
  di kolom wilayah `users`), verifikasi KTP (`nik`, `ktp_image`, `is_verified`).
- **Pelanggan**: punya `cart`, `addresses` (banyak), `orders`.
- **Admin**: kelola pengguna, kategori, hero slide, verifikasi petani, toggle produk. Selalu
  dianggap profil & email terverifikasi (lihat `User::hasVerifiedEmail`, `hasCompleteProfile`).

Cek role **selalu** via helper `User::isPetani()/isPelanggan()/isAdmin()` atau middleware
`role:petani`. Jangan bandingkan string mentah.

### 4.2 Alamat — dua sumber berbeda (sering salah!)
- **Pelanggan** → tabel `addresses` (banyak alamat, satu `is_default`). Snapshot via
  `Address::snapshot()`.
- **Petani** → kolom wilayah langsung di tabel `users` (alamat toko tunggal).
- Gunakan `User::addressSnapshot()` yang sudah memilih sumber benar per role. **Jangan** baca
  kolom wilayah mentah dari `users` untuk pelanggan.

### 4.3 Status pesanan — `App\Enums\OrderStatus`
State machine (string value): `pending → dibayar → dikirim → selesai`, atau `→ batal`.

| Status | Arti | Siapa yang transisi |
|---|---|---|
| `pending` | Menunggu pembayaran Midtrans | Order baru (online) |
| `dibayar` | Sudah dibayar / dikonfirmasi (label pelanggan: "Dikemas") | Midtrans callback, atau order pickup langsung |
| `dikirim` | Petani sudah mengirim | Petani (`ship`) |
| `selesai` | Diterima / selesai | Petani (`complete`) atau pelanggan konfirmasi terima |
| `batal` | Dibatalkan / expired | Auto-expire, petani cancel, Midtrans cancel/deny/expire |

**Aturan transisi (tegakkan di controller):**
- `ship`: hanya dari `dibayar`. `complete`: hanya dari `dikirim`.
- `complete` menaikkan `products.sold_count` (hanya item milik petani tsb).
- Saat menambah status baru, update **ketiga** method enum: `label()`, `customerLabel()`,
  `badgeClass()`. Jangan biarkan match() incomplete.
- Selalu tampilkan status ke pelanggan via `customerLabel()` (mis. `dibayar` → "Dikemas"),
  ke internal/petani via `label()`.

### 4.4 Metode pembayaran — `App\Enums\PaymentMethod`
- `online` → ongkir normal, bayar via Midtrans Snap, order mulai `pending` dengan `expires_at`.
- `pickup` → ongkir **dipaksa 0** untuk semua toko, order langsung `dibayar`, **stok langsung
  dikurangi saat order dibuat**, tanpa `expires_at`. `payment_status = 'unpaid_pickup'`.
- Parse input user selalu via `PaymentMethod::fromInput()` (fallback aman ke Online).

### 4.5 Pengurangan stok (race-condition sensitive)
- **Online**: stok dikurangi **setelah** Midtrans konfirmasi `capture`/`settlement`
  (`applyMidtransStatus`), di dalam `DB::transaction` + `lockForUpdate()`. **Idempoten** —
  cek `wasPaid` supaya tidak double-decrement saat notifikasi & sync datang bersamaan.
- **Pickup**: stok dikurangi saat order dibuat (`store`), juga dalam transaksi + lock.
- Pakai `Product::withTrashed()->lockForUpdate()` saat decrement (produk bisa soft-deleted
  setelah masuk keranjang). **Jangan** kurangi stok tanpa lock.

### 4.6 Expiry pembayaran
- `Order::PAYMENT_TIMEOUT_MINUTES = 10`. Order `pending` online dapat `expires_at`.
- Tiga lapis penegakan (defense-in-depth) — **jangan hapus salah satu**:
  1. Scheduled command `orders:expire-pending` (tiap menit, `routes/console.php`).
  2. Lazy `Order::expireIfDue()` saat user buka halaman pembayaran.
  3. Bulk `Order::expireOverdue()` saat render daftar pesanan.
- Pickup/COD **tidak pernah** expired (`expires_at` null).

---

## 5. Ongkir (RajaOngkir/Komerce) — aturan ketat

- **Tidak ada fallback tarif lokal.** Kalau API key kosong / mapping `regencies.rajaongkir_id`
  belum ada / API gagal → ongkir `available: false` dan checkout **diblokir** di view. Jangan
  menambahkan tarif tebakan/hardcoded sebagai "pengganti".
- Alur: `CheckoutSummaryService::build()` mengelompokkan keranjang **per toko (`product->user_id`)**,
  hitung berat & subtotal per toko, lalu panggil `ShippingService::calculateShipping()` per toko.
- Ongkir **selalu dihitung ulang di server** saat `store()` pembayaran — jangan percaya nilai
  ongkir dari form client. `shipping[storeId] = "courier:service"` (mis. `jne:REG`) hanya
  memilih opsi, bukan menentukan harga.
- Kode opsi terstandar: `ShippingService::optionCode($courier, $service)` → `"jne:REG"`
  (courier lower, service upper). Default = opsi termurah (`options[0]`).
- Cache: hanya **hasil sukses** yang di-cache (`RAJAONGKIR_CACHE_TTL`, default 6 jam). Kegagalan
  TIDAK di-cache supaya retry langsung hit API. Jangan ubah ini.
- Tiap kurir = 1 panggilan API per rute (kuota free tier terbatas). Tambah kurir lewat config
  `RAJAONGKIR_COURIERS` (CSV), bukan hardcode.
- Snapshot ongkir per toko disimpan ke `order_shippings` saat order dibuat (audit + invoice).

---

## 6. Pembayaran Midtrans — aturan keamanan

- Config Midtrans di-set di constructor `PembayaranController` dari `config('services.midtrans.*')`.
- Webhook `POST /midtrans/notification` **dikecualikan dari CSRF** (lihat `bootstrap/app.php`).
  **Wajib verifikasi signature** (`hash('sha512', order_id+status_code+gross_amount+server_key)`)
  sebelum memproses — jangan pernah trust payload mentah.
- `gross_amount` harus = `sum(item_details.price * quantity)`. Ongkir per toko ditambahkan
  sebagai item terpisah (`SHIP-{id}`) supaya sum cocok. Jaga invariant ini kalau ubah payload.
- Window expiry Snap diselaraskan dengan `order.expires_at`.
- Status update terpusat di `applyMidtransStatus()` — dipanggil dari webhook **dan** dari
  `syncStatusFromMidtrans()` (polling manual). Jangan duplikasi logika mapping status.

---

## 7. Data Wilayah (Provinsi/Kota/Kecamatan)

- Tabel `provinces` / `regencies` / `districts` dengan **primary key string (cuid, 32 char)**,
  bukan auto-increment. Relasi pakai `province_id` / `regency_id` string.
- Akses **selalu** lewat `App\Support\Wilayah` (di-cache per-request untuk hindari N+1).
- `regencies.rajaongkir_id` = mapping ke ID destinasi RajaOngkir, diisi command
  `rajaongkir:sync-cities` (kena batas kuota harian Komerce → dijalankan bertahap).
  Tanpa ini ongkir tidak bisa dihitung.
- **Persistensi mapping**: nilai `rajaongkir_id` TIDAK ada di RegencySeeder. Supaya
  tahan `migrate:fresh --seed` & portabel antar mesin, mapping dibekukan ke
  `RajaongkirMappingSeeder` (terdaftar setelah RegencySeeder di `DatabaseSeeder`).
  Alur tiap habis sync: `php artisan rajaongkir:sync-cities` lalu
  `php artisan rajaongkir:dump-mapping` (regenerate seeder dari DB) → commit.
  JANGAN edit `RajaongkirMappingSeeder` manual; selalu regenerate.
- AJAX dropdown wilayah lewat route `wilayah.cities` / `wilayah.districts`.

---

## 8. Konvensi Controller (ikuti pola persis ini)

Contoh kanonik: `Petani\ProdukController`, `Petani\PesananController`.

- **Index dengan filter/sort/paginate** pakai pola `SORT_MAP` const + `->when()` chaining +
  `->withQueryString()`. `per_page` divalidasi whitelist `[10,25,50,100]`. Tiru pola ini untuk
  setiap listing baru.
- **Otorisasi kepemilikan**: `abort_unless($model->user_id === Auth::id(), 403)` atau
  helper privat `authorizeOrder()`. **Selalu** cek kepemilikan sebelum update/delete — jangan
  andalkan route saja.
- **Validasi**: inline `$request->validate([...])` untuk yang sederhana; pindah ke FormRequest
  (`app/Http/Requests`) kalau dipakai ulang atau kompleks. Sertakan **pesan error Indonesia**
  custom seperti di `ProdukController::validateProduk()`.
- **Redirect** selalu dengan flash `->with('success'|'error', '...')` (Indonesia). Pesan sukses
  → `success`, gagal/blok → `error`.
- **Transaksi DB** (`DB::transaction`) untuk operasi multi-tabel yang harus atomik (buat order +
  items + shippings + kosongkan cart).

---

## 9. Konvensi Model

- `protected $fillable` eksplisit (tidak pakai `$guarded`). Tambahkan kolom baru ke `$fillable`.
- Casts via **method** `protected function casts(): array`, bukan properti. Enum di-cast ke
  class enum (`'status' => OrderStatus::class`). Uang `decimal:2`, berat `decimal:3`.
- Accessor pakai **`Attribute`** modern (`protected function imageUrl(): Attribute`), bukan
  `getXxxAttribute`. Lihat `Product::imageUrl`, `Order::code`, `User::ktpImageUrl`.
- Relasi diberi return type (`HasMany`, `BelongsTo`, `HasOne`).
- `Product` pakai **SoftDeletes** + route key `slug` (auto-generate unik di `booted()`).
  Saat akses produk yang mungkin terhapus (mis. di order lama) pakai `withTrashed()`.
- `Product::scopeActive()` untuk filter `is_active`. Pakai scope, jangan ulang `where('is_active', true)`.
- Memoisasi per-request untuk count yang dipanggil berulang di view (lihat
  `User::petaniIncomingOrdersCount` static memo). Tiru pola ini, jangan query di loop Blade.

---

## 10. Upload File

- **Selalu** lewat `App\Support\PublicUpload::store($file, $folder)` → simpan ke
  `public/uploads/{folder}`, return path relatif `"uploads/..."`. Hapus via `PublicUpload::delete()`.
- Ekstensi diizinkan: `jpg, jpeg, png, webp`. Validasi `image|mimes:...|max:4096` (4MB) di request.
- **Bukan** `Storage::disk('public')` / symlink untuk upload baru. Path lama `storage/...`
  di-handle graceful fallback di accessor (mis. `User::ktpImageUrl`) — jangan dihapus.
- Galeri produk: maks 5 gambar tambahan (`ProdukController::MAX_GALLERY`), di tabel `product_images`
  dengan `sort_order`.

---

## 11. Frontend / Blade

- Layout: `<x-layouts.app>` (dashboard role, ada sidebar), `<x-layouts.storefront>` (katalog publik),
  `<x-layouts.guest>` (auth). Halaman role taruh di `resources/views/pages/{role}/...`.
- UI **Tabler** (class `bg-green-lt`, `page`, `card`, dll). Ikon **Tabler Icons** webfont.
- **Konfirmasi aksi destruktif**: form `<form ... data-confirm="Yakin hapus?">` — sudah ada
  global handler SweetAlert di `layouts/app`. Jangan tulis `confirm()` JS sendiri.
- Flash message dirender via `@include('partials.flash-popup')` (SweetAlert). Cukup kirim
  `->with('success'|'error', ...)` dari controller.
- Badge status pesanan: `$order->status->badgeClass()` + `->customerLabel()`/`->label()`.
- Format: berat via `App\Support\WeightFormatter`, uang `number_format($n, 0, ',', '.')`,
  tanggal locale ID (`translatedFormat`, sudah di-set di `AppServiceProvider`).

---

## 12. Perintah Penting

```bash
# Setup
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# Dev (server + queue + logs + vite sekaligus)
composer run dev
# atau terpisah: php artisan serve | npm run dev | php artisan queue:listen

# Test
php artisan test                        # semua
php artisan test --filter ShippingService

# Format (WAJIB sebelum commit) — Laravel Pint, preset default
./vendor/bin/pint

# Wajib jalan di cron prod (tiap menit) untuk auto-expire order:
php artisan schedule:run
```

- **Style**: Laravel Pint preset `laravel`. Indentasi 4 spasi, LF, final newline (`.editorconfig`).
  Jalankan `pint` sebelum commit, jangan format manual yang menyimpang.

---

## 13. Testing

- PHPUnit. Unit test untuk Service murni (`tests/Unit/ShippingServiceTest`,
  `RajaOngkirClientTest`) — **mock** `RajaOngkirClient`, jangan hit API beneran.
- Feature test untuk alur HTTP (auth, profile).
- **Wajib** tambah/Update test saat mengubah logika di `Services/` (ongkir, checkout, pembayaran).
  Service dirancang agar mudah di-test via constructor injection (`new ShippingService($mockClient)`).

---

## 14. Larangan & Gotcha (baca sebelum PR)

1. **Jangan** taruh logika bisnis berat / panggilan API di controller atau Blade — pindah ke Service.
2. **Jangan** hardcode tarif ongkir sebagai fallback. Tidak tersedia = blokir checkout.
3. **Jangan** trust nilai ongkir/total dari form client — selalu hitung ulang di server.
4. **Jangan** proses webhook Midtrans tanpa verifikasi signature.
5. **Jangan** decrement stok tanpa `lockForUpdate()` dan tanpa cek idempotensi.
6. **Jangan** hardcode string role/status — pakai Enum (`UserRole`, `OrderStatus`, `PaymentMethod`).
7. **Jangan** campur istilah Indonesia/Inggris di luar konvensi tabel di bagian 2.
8. **Jangan** baca alamat pelanggan dari kolom `users` — pakai `addressSnapshot()`.
9. **Jangan** hapus salah satu dari 3 lapis auto-expire order.
10. **Jangan** pakai `Storage` symlink untuk upload baru — pakai `PublicUpload`.
11. **Jangan** lupa update ketiga method `OrderStatus` saat menambah status.
12. Saat menambah kolom DB: buat migration baru (jangan edit migration lama yang sudah jalan),
    tambahkan ke `$fillable`, dan cast bila perlu.
