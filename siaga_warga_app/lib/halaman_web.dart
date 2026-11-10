import 'dart:async';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

import 'halaman_alamat.dart';
import 'konfigurasi.dart';

/// Aplikasi Laravel Siaga Warga dibungkus di dalam aplikasi Android ini.
class HalamanWeb extends StatefulWidget {
  const HalamanWeb({super.key, required this.alamat});

  final String alamat;

  @override
  State<HalamanWeb> createState() => _HalamanWebState();
}

class _HalamanWebState extends State<HalamanWeb> {
  late final WebViewController _web;
  bool _memuat = true;
  bool _gagal = false;
  String _pesan = '';
  Timer? _ulang;

  @override
  void initState() {
    super.initState();
    _web = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(const Color(0xFFF6F8FB))
      ..setNavigationDelegate(NavigationDelegate(
        onPageStarted: (_) {
          if (mounted) setState(() => _memuat = true);
        },
        onPageFinished: (_) {
          if (mounted) setState(() => _memuat = false);
        },
        onWebResourceError: (galat) {
          // sub-sumber (gambar, css) sering gagal tanpa merusak halaman
          if (galat.isForMainFrame == false) return;
          if (mounted) {
            setState(() {
              _gagal = true;
              _memuat = false;
              _pesan = _terjemahkan(galat);
            });
          }
        },
      ));
    _muat();
  }

  @override
  void dispose() {
    _ulang?.cancel();
    super.dispose();
  }

  String _terjemahkan(WebResourceError galat) {
    final kode = galat.errorCode;
    if (kode == -2 || kode == -1009) return 'Tidak ada koneksi internet.';
    if (kode == -8) return 'Server tidak ditemukan. Periksa alamat server.';
    if (kode == -6) return 'Alamat server salah atau tidak bisa dihubungi.';
    if (kode == -4) return 'Gagal menyambung ke server.';
    if (kode == -5) return 'Server memakai sertifikat yang tidak dikenal. Hubungi pengurus.';
    if (kode == -11) return 'Sertifikat server bermasalah. Hubungi pengurus.';
    return galat.description;
  }

  void _muat() {
    _ulang?.cancel();
    setState(() {
      _gagal = false;
      _pesan = '';
      _memuat = true;
    });
    _web.loadRequest(Uri.parse(widget.alamat));
  }

  Future<bool> _kembali() async {
    if (await _web.canGoBack()) {
      await _web.goBack();
      return false;
    }
    return true;
  }

  Future<void> _gantiAlamat() async {
    final lanjut = await showDialog<bool>(
      context: context,
      builder: (konteks) => AlertDialog(
        title: const Text('Ganti alamat server?'),
        content: Text('Saat ini: ${widget.alamat}\n\nWarga harus masuk ulang setelah diganti.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(konteks, false), child: const Text('Batal')),
          FilledButton(onPressed: () => Navigator.pop(konteks, true), child: const Text('Ganti')),
        ],
      ),
    );
    if (lanjut != true || !mounted) return;
    await Konfigurasi.hapus();
    if (!mounted) return;
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (_) => const HalamanAlamat()),
    );
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (sudahKembali, _) async {
        if (sudahKembali) return;
        if (await _kembali()) {
          if (!context.mounted) return;
          Navigator.of(context).maybePop();
        }
      },
      child: Scaffold(
        appBar: AppBar(
          title: Row(
            children: [
              const Icon(Icons.campaign_rounded, size: 22),
              const SizedBox(width: 8),
              const Text('Siaga Warga', style: TextStyle(fontWeight: FontWeight.bold)),
              const Spacer(),
              if (_memuat)
                const SizedBox(
                  width: 16, height: 16,
                  child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                ),
            ],
          ),
          backgroundColor: const Color(0xFF1D4ED8),
          foregroundColor: Colors.white,
          actions: [
            IconButton(
              tooltip: 'Muat ulang',
              onPressed: _muat,
              icon: const Icon(Icons.refresh),
            ),
            IconButton(
              tooltip: 'Ganti alamat server',
              onPressed: _gantiAlamat,
              icon: const Icon(Icons.settings),
            ),
          ],
        ),
        body: _gagal
            ? _pesanGagal(context)
            : WebViewWidget(controller: _web),
      ),
    );
  }

  Widget _pesanGagal(BuildContext context) {
    final tema = Theme.of(context);
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.wifi_off_rounded, size: 56, color: tema.colorScheme.error),
            const SizedBox(height: 14),
            Text('Tidak bisa membuka Siaga Warga',
                textAlign: TextAlign.center,
                style: tema.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            Text(_pesan, textAlign: TextAlign.center, style: tema.textTheme.bodyMedium),
            const SizedBox(height: 6),
            Text(widget.alamat,
                textAlign: TextAlign.center,
                style: tema.textTheme.bodySmall?.copyWith(color: Colors.black54)),
            const SizedBox(height: 20),
            FilledButton.icon(
              onPressed: _muat,
              icon: const Icon(Icons.refresh),
              label: const Text('Coba lagi'),
              style: FilledButton.styleFrom(minimumSize: const Size(200, 48)),
            ),
            const SizedBox(height: 8),
            TextButton(onPressed: _gantiAlamat, child: const Text('Ganti alamat server')),
            if (Platform.isAndroid)
              TextButton(
                onPressed: () => exit(0),
                child: const Text('Tutup aplikasi'),
              ),
          ],
        ),
      ),
    );
  }
}
