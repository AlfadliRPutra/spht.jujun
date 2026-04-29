<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
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
     * Snapshot alamat (wilayah administratif + full address) yang dipakai
     * ShippingService sebagai parameter storeAddress / buyerAddress.
     */
    public function addressSnapshot(): array
    {
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
        return ! empty($this->city_id) && ! empty($this->district_id);
    }
}
