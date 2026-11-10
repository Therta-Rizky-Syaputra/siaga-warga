<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengumuman extends Model
{
    use HasFactory;

    protected $table = 'pengumumans';

    protected $fillable = ['user_id', 'judul', 'isi', 'berlaku_sampai'];

    protected function casts(): array
    {
        return ['berlaku_sampai' => 'datetime'];
    }

    public function penulis(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeAktif(Builder $q): Builder
    {
        return $q->where(fn (Builder $q) => $q
            ->whereNull('berlaku_sampai')
            ->orWhere('berlaku_sampai', '>=', now()));
    }
}
