import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:siaga_warga_app/halaman_alamat.dart';
import 'package:siaga_warga_app/konfigurasi.dart';

void main() {
  testWidgets('layar alamat tampil dan menolak alamat kosong', (tester) async {
    await tester.pumpWidget(const MaterialApp(home: HalamanAlamat()));

    expect(find.text('Siaga Warga'), findsOneWidget);
    expect(find.text('Buka Siaga Warga'), findsOneWidget);

    await tester.tap(find.text('Buka Siaga Warga'));
    await tester.pumpAndSettle();

    expect(find.text('Alamat server belum diisi'), findsOneWidget);
  });

  test('alamat bawaan kosong bila tidak ditanam saat build', () {
    // tanpa --dart-define=ALAMAT_SERVER, aplikasi meminta alamat ke warga
    expect(Konfigurasi.alamatBawaan, '');
  });

  test('alamat server dinormalkan dengan benar', () {
    expect(Konfigurasi.normalisasi('siaga.warga.id'), 'https://siaga.warga.id');
    expect(Konfigurasi.normalisasi('  siaga.warga.id/  '), 'https://siaga.warga.id');
    expect(Konfigurasi.normalisasi('192.168.1.9:8000'), 'http://192.168.1.9:8000');
    expect(Konfigurasi.normalisasi('http://10.0.2.2:8095'), 'http://10.0.2.2:8095');
    expect(Konfigurasi.normalisasi('localhost:8000'), 'http://localhost:8000');
    expect(Konfigurasi.normalisasi('https://siaga.warga.id'), 'https://siaga.warga.id');
  });
}
