# Siaga Warga

Platform pengaduan fasilitas umum warga RT/RW. Tugas Kelompok 6 — Pertemuan 3, mata kuliah
Komputer dan Masyarakat, Program Studi Informatika, Fakultas Ilmu Komputer, Universitas
Singaperbangsa Karawang.

Aplikasi web **Laravel 13 + Vite + Tailwind CSS 4 + SQLite** dengan tiga aktor:

| Aktor | Hak akses |
|---|---|
| Warga | Kirim laporan (kategori, deskripsi, lokasi acuan, foto) dan pantau progres status |
| Pengurus RT/RW | Dashboard ringkasan, verifikasi & ubah status, tetapkan prioritas, tanggapi, publikasi pengumuman |
| Admin Sistem | Kelola basis data pengguna (tambah, ubah peran, hapus) |

## Menjalankan

```bash
composer install
npm install
cp .env.example .env        # DB_CONNECTION=sqlite
php artisan key:generate
php artisan migrate --seed  # membuat database/database.sqlite + data contoh
php artisan storage:link    # agar foto laporan bisa diakses
npm run build               # atau: npm run dev
php artisan serve
```

Buka `http://127.0.0.1:8000`.

## Akun demo (kata sandi: `password`)

| Peran | Email |
|---|---|
| Admin Sistem | admin@siagawarga.test |
| Pengurus RT/RW | pengurus@siagawarga.test |
| Warga | warga@siagawarga.test |

## Alur sistem

- **Warga** → `/laporan/baru` (kirim) → `/laporan` (pantau) → `/laporan/{id}` (linimasa status + tanggapan).
  Status berjalan: `dikirim` → `diverifikasi` → `diproses` → `selesai` (atau `ditolak`).
- **Pengurus** → `/masuk/laporan` (antrean, diurut prioritas tinggi lalu paling lama menunggu)
  → `/masuk/laporan/{id}` (ubah status/prioritas + tanggapan). Status `diproses`/`selesai`
  mencatat `ditangani_pada` sebagai metrik waktu respons.
- **Admin** → `/admin/pengguna`.

## Struktur data (ERD)

```
users ──1:N── laporans ──N:1── kategori_masalahs
  │              │
  │              └──1:N── tanggapans ──N:1── users
  └──1:N── pengumumans
```

`laporans.ditangani_oleh` menyimpan pengurus yang menangani laporan.

## Indikator keberhasilan yang dipetakan ke fitur

| Indikator | Wujud di sistem |
|---|---|
| Zero Missed Report | Setiap laporan punya nomor tiket (`SW-0001`) dan wajib punya status |
| Efisiensi waktu respons | `ditangani_pada` diisi saat status pertama kali masuk `diproses`/`selesai` |
| Kualitas teknis (UAT) | 9 uji fitur pada `tests/Feature` |

## Pengujian

```bash
php artisan test
```

## Tangkapan layar

`docs/screenshot/` — hasil aplikasi yang benar-benar dijalankan dan diambil lewat peramban.
