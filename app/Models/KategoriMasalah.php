<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriMasalah extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'deskripsi'];

    public function laporans(): HasMany
    {
        return $this->hasMany(Laporan::class);
    }
}
