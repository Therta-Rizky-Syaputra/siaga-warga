<?php

namespace App\Support;

final class StatusLaporan
{
    public const DIKIRIM = 'dikirim';

    public const DIVERIFIKASI = 'diverifikasi';

    public const DIPROSES = 'diproses';

    public const SELESAI = 'selesai';

    public const DITOLAK = 'ditolak';

    public static function semua(): array
    {
        return [self::DIKIRIM, self::DIVERIFIKASI, self::DIPROSES, self::SELESAI, self::DITOLAK];
    }

    public static function label(string $status): string
    {
        return match ($status) {
            self::DIKIRIM => 'Dikirim',
            self::DIVERIFIKASI => 'Diverifikasi',
            self::DIPROSES => 'Diproses',
            self::SELESAI => 'Selesai',
            self::DITOLAK => 'Ditolak',
            default => $status,
        };
    }

    /** Alur maju normal; ditolak bercabang dan menghentikan proses. */
    public static function urutan(): array
    {
        return [self::DIKIRIM, self::DIVERIFIKASI, self::DIPROSES, self::SELESAI];
    }
}
