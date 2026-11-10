<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\Peran;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'rt_rw', 'telepon'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** @return HasMany<Laporan, $this> */
    public function laporans(): HasMany
    {
        return $this->hasMany(Laporan::class);
    }

    /** @return HasMany<Pengumuman, $this> */
    public function pengumumans(): HasMany
    {
        return $this->hasMany(Pengumuman::class);
    }

    public function isWarga(): bool
    {
        return $this->role === Peran::WARGA;
    }

    /** Pengurus RT/RW atau admin: boleh memverifikasi & menangani semua laporan. */
    public function isPengelola(): bool
    {
        return in_array($this->role, [Peran::PENGURUS, Peran::ADMIN], true);
    }

    public function isAdmin(): bool
    {
        return $this->role === Peran::ADMIN;
    }
}
