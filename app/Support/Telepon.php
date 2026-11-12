<?php

namespace App\Support;

final class Telepon
{
    /** Ubah "0812-3456 7890", "+62 812 3456 7890", "62812..." menjadi 6281234567890. */
    public static function normalisasi(string $nomor): string
    {
        $digit = preg_replace('/\D+/', '', $nomor) ?? '';

        if (str_starts_with($digit, '62')) {
            return $digit;
        }

        if (str_starts_with($digit, '0')) {
            return '62'.substr($digit, 1);
        }

        if (str_starts_with($digit, '8')) {
            return '62'.$digit;
        }

        return $digit;
    }

    /** Tampilkan kembali dalam bentuk yang akrab dibaca warga: 0812-3456-7890. */
    public static function tampilkan(string $nomor): string
    {
        $lokal = str_starts_with($nomor, '62') ? '0'.substr($nomor, 2) : $nomor;

        return implode('-', array_filter([
            substr($lokal, 0, 4),
            substr($lokal, 4, 4),
            substr($lokal, 8),
        ], fn ($bagian) => $bagian !== ''));
    }
}
