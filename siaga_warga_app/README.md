# Siaga Warga — Aplikasi Android

Pembungkus Android (Flutter + WebView) untuk aplikasi web Siaga Warga.
Warga memasang satu berkas APK, ikonnya muncul di layar utama, dan seluruh isinya
berjalan dari server RT/RW.

## Isi repositori ini

```
lib/konfigurasi.dart     alamat server + penyeragaman penulisannya
lib/halaman_alamat.dart  layar pertama bila alamat belum ditanam
lib/halaman_web.dart     pembungkus WebView + pesan galat berbahasa Indonesia
android/                 konfigurasi Android (izin, tanda tangan, aturan jaringan)
docs/BANGUN-APK.md       panduan membangun APK langkah demi langkah
test/                    uji widget dan uji penyeragaman alamat
```

## Membangun

```bash
flutter pub get
flutter analyze
flutter test

# alamat server ditanam, warga tidak perlu mengetik apa pun
flutter build apk --release --dart-define=ALAMAT_SERVER=siaga.contoh.id

# berkas lebih kecil untuk dibagikan lewat WhatsApp
flutter build apk --release --split-per-abi --dart-define=ALAMAT_SERVER=siaga.contoh.id
```

Hasilnya di `build/app/outputs/flutter-apk/`.

## Tanda tangan

APK rilis ditandatangani memakai `android/key.properties` dan berkas `.jks`.
**Keduanya tidak ikut masuk repositori ini** — simpan cadangannya di tempat aman.
Tanpa berkas itu tidak mungkin merilis pembaruan yang bisa dipasang menimpa APK lama.

Panduan lengkap: `docs/BANGUN-APK.md`.
