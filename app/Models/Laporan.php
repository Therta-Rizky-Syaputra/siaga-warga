<?php

namespace App\Models;

use App\Support\StatusLaporan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Laporan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'kategori_masalah_id', 'judul', 'deskripsi', 'lokasi',
        'foto', 'prioritas', 'status', 'ditangani_oleh', 'ditangani_pada',
    ];

    protected function casts(): array
    {
        return ['ditangani_pada' => 'datetime'];
    }

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriMasalah::class, 'kategori_masalah_id');
    }

    public function penanggungJawab(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditangani_oleh');
    }

    public function tanggapans(): HasMany
    {
        return $this->hasMany(Tanggapan::class)->oldest();
    }

    public function nomorTiket(): string
    {
        return 'SW-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        return StatusLaporan::label($this->status);
    }

    public function scopePrioritasTinggi(Builder $q): Builder
    {
        return $q->where('prioritas', 'tinggi')->whereNotIn('status', [StatusLaporan::SELESAI, StatusLaporan::DITOLAK]);
    }
}
