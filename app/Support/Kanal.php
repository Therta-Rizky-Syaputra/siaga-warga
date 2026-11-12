<?php

namespace App\Support;

use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Penanda perubahan data sederhana di atas cache.
 * Satu angka naik setiap ada laporan/pengumuman berubah; klien SSE membandingkannya.
 */
final class Kanal
{
    public function __construct(private Cache $cache) {}

    public static function kunci(): string
    {
        return 'kanal:versi';
    }

    public function versi(): int
    {
        return (int) $this->cache->get(self::kunci(), 1);
    }

    public function tandaiBerubah(): void
    {
        $this->cache->forever(self::kunci(), $this->versi() + 1);
    }
}
