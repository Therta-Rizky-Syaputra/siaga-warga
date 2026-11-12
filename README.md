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

Masuk memakai **nomor HP**. Format bebas: `0812…`, `62812…`, atau `+62 812…`.

| Peran | Nomor HP |
|---|---|
| Admin Sistem | 0812-0000-0001 |
| Pengurus RT/RW | 0812-0000-0002 |
| Warga | 0812-0000-0003 |

Warga mendaftar sendiri dengan nomor HP + kata sandi (email opsional). Foto bukti
disimpan **privat** — hanya bisa dibuka pelapor dan pengurus RT/RW.

## Real-time

- **Pengurus** (`/dashboard`) memakai Server-Sent Events: laporan warga muncul tanpa refresh.
- **Warga** memakai denyut ringan tiap 10 detik dan berhenti saat layar tidak dilihat.
- Aplikasi bisa dipasang ke layar utama HP (PWA); ada halaman "tidak ada sinyal" saat jaringan putus.

> Catatan: `php artisan serve` hanya satu proses, sehingga satu koneksi SSE menahan permintaan lain.
> Untuk menguji real-time lokal, pakai Apache/Laragon (seperti VPS).

## Pemasangan ke VPS

Lihat `deploy/DEPLOY.md` (nginx + HTTPS + PHP-FPM + antrean + cadangan harian).

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
php artisan test     # 19 uji: autentikasi nomor HP, alur laporan, real-time, izin foto, PWA
```

## Dokumen perancangan

- `docs/Perancangan-Sistem-Siaga-Warga.docx` (dan `.pdf`) — dokumen perancangan: use case diagram,
  flowchart per aktor, ERD & skema tabel, wireframe UI/UX, spesifikasi teknis, rencana UAT.
- `docs/diagram/` — gambar diagram (PNG) hasil skrip `docs/gambar_diagram.py`.
- `docs/buat_dokumen.py` — skrip penyusun dokumen dari gambar tersebut.

## Tangkapan layar

`docs/screenshot/` — hasil aplikasi yang benar-benar dijalankan dan diambil lewat peramban.
