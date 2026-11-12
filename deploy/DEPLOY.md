# Pemasangan Siaga Warga di VPS

Panduan singkat untuk satu VPS (Ubuntu 22.04/24.04) + domain + HTTPS.
Perintah dijalankan berurutan; ganti `siaga.contoh.id` dengan domain Anda.

## 0. Yang perlu disiapkan

- VPS 1 vCPU / 1 GB RAM sudah cukup untuk satu kecamatan.
- Domain/subdomain yang titik A-nya diarahkan ke IP VPS, misal `siaga.contoh.id`.
- Akses SSH dengan sudo.

## 1. Paket dasar

```bash
sudo apt update
sudo apt install -y nginx php8.3-fpm php8.3-cli php8.3-sqlite3 php8.3-mbstring \
    php8.3-xml php8.3-curl php8.3-zip php8.3-gd git unzip certbot python3-certbot-nginx
curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer
```

## 2. Ambil kode dan siapkan aplikasi

```bash
sudo mkdir -p /var/www && cd /var/www
sudo git clone <url-repositori-anda> siaga-warga
cd siaga-warga
sudo composer install --no-dev --optimize-autoloader
sudo cp .env.production.example .env
sudo nano .env                      # isi APP_KEY, APP_URL, dan DB_DATABASE
sudo php artisan key:generate
sudo touch database/database.sqlite
sudo php artisan migrate --force
sudo php artisan db:seed --force    # opsional: data contoh
```

## 3. Build aset (di komputer, lalu unggah)

Jalankan di komputer pengembang, karena VPS sering tidak perlu Node.js:

```bash
npm ci && npm run build
# lalu unggah isi folder public/build ke VPS: /var/www/siaga-warga/public/build
```

## 4. Izin berkas — sumber paling umum aplikasi "error 500"

```bash
sudo chown -R www-data:www-data /var/www/siaga-warga
sudo find /var/www/siaga-warga -type d -exec chmod 755 {} \;
sudo find /var/www/siaga-warga -type f -exec chmod 644 {} \;
sudo chmod -R 775 /var/www/siaga-warga/storage /var/www/siaga-warga/bootstrap/cache
```

## 5. Nginx + HTTPS

```bash
sudo cp deploy/nginx-siaga-warga.conf /etc/nginx/sites-available/siaga-warga
sudo ln -s /etc/nginx/sites-available/siaga-warga /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
sudo certbot --nginx -d siaga.contoh.id     # sertifikat HTTPS otomatis
```

Setelah HTTPS aktif, pastikan `.env` berisi `SESSION_SECURE_COOKIE=true` lalu:

```bash
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
```

## 6. Pekerja antrean (notifikasi keluhan)

```bash
sudo cp deploy/siaga-warga.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable --now siaga-warga
sudo systemctl status siaga-warga --no-pager
```

## 7. Cadangkan data tiap malam (laporan warga jangan sampai hilang)

```bash
sudo crontab -e
# tambahkan:
0 2 * * * cp /var/www/siaga-warga/database/database.sqlite /var/backups/siaga-warga-$(date +\%F).sqlite
```

## 7b. Menguji di komputer sendiri

`php artisan serve` hanya menjalankan **satu** proses, jadi satu koneksi SSE akan menahan
permintaan lain dan halaman terasa macet. Untuk menguji real-time secara lokal:

```bash
php artisan serve --host=127.0.0.1 --port=8000 --no-reload   # PHP_CLI_SERVER_WORKERS=4
```

atau langsung pakai Laragon/XAMPP (Apache + banyak proses) seperti di VPS.
Di VPS, PHP-FPM sudah multi-proses sehingga tidak ada masalah ini.

## 8. Daftar periksa sebelum warga memakai

- [ ] `https://siaga.contoh.id` terbuka dan ada gembok HTTPS.
- [ ] Daftar akun warga baru berhasil, lalu masuk memakai nomor HP.
- [ ] Kirim laporan uji + foto, dan foto bisa dibuka kembali oleh warga itu.
- [ ] Layar pengurus: laporan uji muncul **tanpa refresh**.
- [ ] Warga lain membuka `/laporan/{id}` milik orang lain → halaman 403.
- [ ] Di HP: menu browser → "Tambahkan ke layar utama", ikon SiagaWarga muncul.
- [ ] Cabut data selular, buka aplikasi → halaman "Tidak ada sinyal" muncul, bukan layar putih.
- [ ] `APP_DEBUG=false` (halaman error tidak menampilkan isi kode).
- [ ] Setelah ganti APP_ENV: `php artisan config:cache route:cache view:cache`.
