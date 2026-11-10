<?php

namespace App\Support;

final class Peran
{
    public const WARGA = 'warga';

    public const PENGURUS = 'pengurus';

    public const ADMIN = 'admin';

    public static function semua(): array
    {
        return [self::WARGA, self::PENGURUS, self::ADMIN];
    }

    public static function label(string $peran): string
    {
        return match ($peran) {
            self::WARGA => 'Warga',
            self::PENGURUS => 'Pengurus RT/RW',
            self::ADMIN => 'Admin Sistem',
            default => $peran,
        };
    }
}
