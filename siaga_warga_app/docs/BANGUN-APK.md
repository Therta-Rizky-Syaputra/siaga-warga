# Membangun APK Siaga Warga

APK ini adalah **pembungkus Android** untuk aplikasi web Siaga Warga (Laravel).
Warga memasang satu berkas APK, ikonnya muncul di layar utama, dan seluruh isinya
berjalan dari server RT/RW.

## 1. Yang perlu terpasang di komputer pembangun

- Flutter 3.44+ (`flutter doctor` harus hijau untuk Android)
- Android SDK (build-tools + platform 35/36) dan JDK 17+ (JDK bawaan Android Studio cukup)

## 2. Membangun APK

```bash
cd siaga_warga_app
flutter pub get

# OPSI A — alamat server ditanam, warga tidak perlu mengetik apa pun (paling disarankan)
flutter build apk --release --dart-define=ALAMAT_SERVER=siaga.contoh.id

# OPSI B — tanpa ditanam, warga mengisi alamat server saat pertama membuka
flutter build apk --release
```

Hasilnya: `build/app/outputs/flutter-apk/app-release.apk` (sekitar 47 MB).

Bila ingin berkas lebih kecil untuk dibagikan lewat WhatsApp, bangun per-arsitektur:

```bash
flutter build apk --release --split-per-abi --dart-define=ALAMAT_SERVER=siaga.contoh.id
# menghasilkan app-arm64-v8a-release.apk (± 15 MB) — pakai ini untuk HP keluaran 2017 ke atas
```

## 3. Kunci penandatanganan

APK rilis ditandatangani dengan `android/key.properties` + `android/siaga-warga.jks`.
**Berkas itu tidak boleh masuk git** (sudah ada di `.gitignore`).

Simpan cadangan `siaga-warga.jks` dan kata sandinya di tempat aman: tanpa berkas itu
Anda tidak bisa merilis pembaruan yang bisa dipasang di atas APK lama.

Bila `key.properties` belum ada, build tetap jalan memakai kunci debug — cukup untuk
uji coba, **jangan** dipakai untuk dibagikan ke warga.

## 4. Cara warga memasang

1. Kirim berkas `app-release.apk` lewat WhatsApp/Google Drive ke HP warga.
2. Buka berkas itu di HP → Android menampilkan peringatan "sumber tidak dikenal" →
   pilih **Setelan** → izinkan pemasangan dari aplikasi itu (WhatsApp/Files).
3. Tekan **Pasang**, lalu **Buka**. Ikon **Siaga Warga** muncul di layar utama.
4. Bila alamat server belum ditanam, isi pada layar pertama, contoh `siaga.contoh.id`,
   lalu tekan **Buka Siaga Warga**.

Cara mengubah alamat server kemudian: tombol gerigi di kanan atas.

## 5. Daftar periksa sebelum dibagikan ke warga

- [ ] `flutter analyze` bersih dan `flutter test` lulus.
- [ ] APK terpasang di HP uji (minimal Android 7).
- [ ] Login nomor HP berhasil, laporan bisa dikirim lengkap dengan foto.
- [ ] Foto bukti bisa dibuka kembali oleh pelapor, dan **tidak** bisa dibuka warga lain.
- [ ] Dashboard pengurus memperbarui angka tanpa menyentuh layar.
- [ ] Cabut data selular → aplikasi menampilkan pesan "Tidak ada koneksi internet",
      bukan layar putih.
- [ ] Tombol kembali Android mengikuti riwayat halaman, tidak langsung menutup aplikasi.

## 6. Membangun ulang saat ada perubahan

```bash
flutter clean && flutter pub get && flutter build apk --release --dart-define=ALAMAT_SERVER=siaga.contoh.id
```

Perubahan pada aplikasi web (Laravel) **tidak** memerlukan APK baru — APK hanya
membungkus. APK baru hanya perlu bila lapisan Android/Flutter-nya yang berubah.
