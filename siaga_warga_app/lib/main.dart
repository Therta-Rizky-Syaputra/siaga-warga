import 'package:flutter/material.dart';

import 'halaman_alamat.dart';
import 'halaman_web.dart';
import 'konfigurasi.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // urutan: alamat yang pernah diisi warga → alamat yang ditanam saat build
  final tersimpan = await Konfigurasi.ambil();
  final bawaan = Konfigurasi.alamatBawaan.trim();
  final alamat = tersimpan ?? (bawaan.isEmpty ? null : Konfigurasi.normalisasi(bawaan));

  runApp(AplikasiSiagaWarga(alamatTersimpan: alamat));
}

class AplikasiSiagaWarga extends StatelessWidget {
  const AplikasiSiagaWarga({super.key, this.alamatTersimpan});

  /// Diisi bila warga sudah pernah memasukkan alamat server.
  final String? alamatTersimpan;

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Siaga Warga',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF1D4ED8)),
        useMaterial3: true,
        scaffoldBackgroundColor: const Color(0xFFF6F8FB),
      ),
      home: alamatTersimpan == null
          ? const HalamanAlamat()
          : HalamanWeb(alamat: alamatTersimpan!),
    );
  }
}
