<?php

namespace Database\Seeders;

use App\Models\KategoriMasalah;
use App\Models\Laporan;
use App\Models\Pengumuman;
use App\Models\Tanggapan;
use App\Models\User;
use App\Support\Peran;
use App\Support\StatusLaporan;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin Sistem', 'telepon' => '6281200000001', 'role' => Peran::ADMIN,
        ]);
        $pengurus = User::factory()->create([
            'name' => 'Budi Santoso (Ketua RT 04)', 'telepon' => '6281200000002',
            'role' => Peran::PENGURUS, 'rt_rw' => 'RT 04 / RW 02',
        ]);
        $warga = User::factory()->create([
            'name' => 'Therta Rizky Syaputra', 'telepon' => '6281200000003',
            'role' => Peran::WARGA, 'rt_rw' => 'RT 04 / RW 02',
        ]);

        $kategoris = collect(['Lampu Jalan', 'Jalan Rusak', 'Sampah & Kebersihan', 'Saluran Air', 'Fasilitas Umum'])
            ->mapWithKeys(fn ($n) => [$n => KategoriMasalah::create(['nama' => $n])]);

        $contoh = [
            ['Lampu Jalan', 'Lampu jalan mati depan Blok C', 'Sudah 3 malam mati total, jalan jadi gelap dan rawan.', 'Depan Blok C No. 12', 'tinggi', StatusLaporan::DIPROSES],
            ['Sampah & Kebersihan', 'Tumpukan sampah dekat pos ronda', 'Sampah tidak diangkut 5 hari, mulai bau.', 'Samping pos ronda RT 04', 'sedang', StatusLaporan::DIVERIFIKASI],
            ['Saluran Air', 'Selokan tersumbat sampah plastik', 'Air menggenang tiap hujan, nyamuk berkembang.', 'Pertigaan Gang Melati', 'tinggi', StatusLaporan::DIKIRIM],
            ['Jalan Rusak', 'Jalan berlubang di depan masjid', 'Lubang cukup dalam, membahayakan pengendara motor.', 'Jl. Kenanga depan Masjid Al-Ikhlas', 'sedang', StatusLaporan::SELESAI],
        ];

        foreach ($contoh as [$kategori, $judul, $deskripsi, $lokasi, $prioritas, $status]) {
            $laporan = Laporan::create([
                'user_id' => $warga->id,
                'kategori_masalah_id' => $kategoris[$kategori]->id,
                'judul' => $judul,
                'deskripsi' => $deskripsi,
                'lokasi' => $lokasi,
                'prioritas' => $prioritas,
                'status' => $status,
                'ditangani_oleh' => $status === StatusLaporan::DIKIRIM ? null : $pengurus->id,
                'ditangani_pada' => $status === StatusLaporan::DIKIRIM ? null : now()->subDay(),
            ]);

            if ($status !== StatusLaporan::DIKIRIM) {
                Tanggapan::create([
                    'laporan_id' => $laporan->id,
                    'user_id' => $pengurus->id,
                    'isi' => 'Laporan sudah kami terima dan sedang ditindaklanjuti petugas.',
                    'status_baru' => $status,
                ]);
            }
        }

        Pengumuman::create([
            'user_id' => $pengurus->id,
            'judul' => 'Kerja Bakti Membersihkan Saluran Air',
            'isi' => 'Diberitahukan kepada seluruh warga bahwa kerja bakti akan dilaksanakan hari Minggu pukul 07.00 WIB di titik pertigaan Gang Melati. Mohon membawa peralatan kebersihan masing-masing.',
            'berlaku_sampai' => now()->addMonth(),
        ]);
    }
}
