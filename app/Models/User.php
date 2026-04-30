<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'no_hp',
        'alamat',
        'province_id',
        'province_name',
        'city_id',
        'city_name',
        'district_id',
        'district_name',
        'nama_usaha',
        'deskripsi_usaha',
        'nik',
        'ktp_image',
        'is_verified',
        'verification_submitted_at',
        'verification_note',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'         => 'datetime',
            'password'                  => 'hashed',
            'is_verified'               => 'boolean',
            'role'                      => UserRole::class,
            'verification_submitted_at' => 'datetime',
        ];
    }

    protected function ktpImageUrl(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->ktp_image) {
                return null;
            }
            if (str_starts_with($this->ktp_image, 'http')) {
                return $this->ktp_image;
            }
            return asset('storage/'.$this->ktp_image);
        });
    }

    public function verificationStatus(): string
    {
        if ($this->is_verified) {
            return 'verified';
        }
        if ($this->verification_submitted_at) {
            return 'pending';
        }
        if ($this->verification_note) {
            return 'rejected';
        }
        return 'not_submitted';
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class)->latest('is_default')->orderBy('id');
    }

    public function defaultAddress(): ?Address
    {
        return $this->addresses()->where('is_default', true)->first()
            ?? $this->addresses()->orderBy('id')->first();
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function isPetani(): bool
    {
        return $this->role === UserRole::Petani;
    }

    public function isPelanggan(): bool
    {
        return $this->role === UserRole::Pelanggan;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Jumlah pesanan masuk yang menunggu diproses petani (status Dibayar).
     * Dimemoisasi per request supaya sidebar + header + dashboard tidak query
     * berulang dalam satu render halaman.
     *
     * @var array<int|string, int>
     */
    private static array $memoIncomingOrders = [];

    public function petaniIncomingOrdersCount(): int
    {
        if (! $this->isPetani()) {
            return 0;
        }

        return self::$memoIncomingOrders[$this->id] ??= Order::query()
            ->where('status', OrderStatus::Dibayar)
            ->whereHas('items.product', fn ($q) => $q->where('user_id', $this->id))
            ->count();
    }

    /**
     * Snapshot alamat untuk ShippingService.
     *
     * - Petani: pakai kolom wilayah di users (alamat toko, tunggal).
     * - Pelanggan: pakai default address dari tabel addresses.
     */
    public function addressSnapshot(): array
    {
        if ($this->isPelanggan()) {
            $addr = $this->defaultAddress();
            return $addr ? $addr->snapshot() : [
                'province_id'   => null,
                'province_name' => null,
                'city_id'       => null,
                'city_name'     => null,
                'district_id'   => null,
                'district_name' => null,
                'full_address'  => null,
            ];
        }

        return [
            'province_id'   => $this->province_id,
            'province_name' => $this->province_name,
            'city_id'       => $this->city_id,
            'city_name'     => $this->city_name,
            'district_id'   => $this->district_id,
            'district_name' => $this->district_name,
            'full_address'  => $this->alamat,
        ];
    }

    public function hasCompleteAddress(): bool
    {
        if ($this->isPelanggan()) {
            return $this->addresses()->exists();
        }
        return ! empty($this->city_id) && ! empty($this->district_id);
    }

    /**
     * Definisi profil lengkap, dipakai oleh middleware EnsureProfileComplete.
     *
     * - Pelanggan: nama, no_hp, dan minimal 1 alamat tersimpan.
     * - Petani:    nama, no_hp, alamat toko lengkap (province/city/district + alamat).
     *              Nama usaha & verifikasi KTP punya alur sendiri (petani.verifikasi),
     *              tidak ikut dicek di sini agar tidak terjadi redirect-loop.
     * - Admin:     selalu dianggap lengkap.
     */
    public function hasCompleteProfile(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if (empty($this->name) || empty($this->no_hp)) {
            return false;
        }

        if ($this->isPelanggan()) {
            return $this->addresses()->exists();
        }

        if ($this->isPetani()) {
            return ! empty($this->city_id)
                && ! empty($this->district_id)
                && ! empty($this->alamat);
        }

        return true;
    }
}
