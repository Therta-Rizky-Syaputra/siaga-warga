import 'package:shared_preferences/shared_preferences.dart';

/// Alamat server Siaga Warga (aplikasi Laravel).
/// Disimpan di HP warga supaya cukup diisi sekali.
class Konfigurasi {
  static const _kunci = 'alamat_server';

  /// Alamat bawaan yang ditanam saat APK dibangun:
  ///   flutter build apk --release --dart-define=ALAMAT_SERVER=siaga.contoh.id
  /// Bila diisi, warga langsung dibawa ke aplikasi tanpa mengetik alamat.
  static const alamatBawaan =
      String.fromEnvironment('ALAMAT_SERVER', defaultValue: '');

  static Future<String?> ambil() async {
    final p = await SharedPreferences.getInstance();
    final nilai = p.getString(_kunci);
    return (nilai == null || nilai.trim().isEmpty) ? null : nilai.trim();
  }

  static Future<void> simpan(String alamat) async {
    final p = await SharedPreferences.getInstance();
    await p.setString(_kunci, normalisasi(alamat));
  }

  static Future<void> hapus() async {
    final p = await SharedPreferences.getInstance();
    await p.remove(_kunci);
  }

  /// "siaga.warga.id" -> "https://siaga.warga.id"; "192.168.1.9:8000" -> "http://192.168.1.9:8000"
  static String normalisasi(String masukan) {
    var t = masukan.trim();
    if (t.isEmpty) return t;
    if (!t.startsWith('http://') && !t.startsWith('https://')) {
      final host = t.split('/').first.split(':').first;
      final lokal = host == 'localhost' ||
          host == '10.0.2.2' ||
          host.startsWith('192.168.') ||
          host.startsWith('10.') ||
          RegExp(r'^172\.(1[6-9]|2\d|3[01])\.').hasMatch(host);
      t = '${lokal ? 'http' : 'https'}://$t';
    }
    while (t.endsWith('/')) {
      t = t.substring(0, t.length - 1);
    }
    return t;
  }
}
