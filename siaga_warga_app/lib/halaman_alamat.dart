import 'package:flutter/material.dart';

import 'halaman_web.dart';
import 'konfigurasi.dart';

/// Layar pertama: warga memasukkan alamat server Siaga Warga.
class HalamanAlamat extends StatefulWidget {
  const HalamanAlamat({super.key});

  @override
  State<HalamanAlamat> createState() => _HalamanAlamatState();
}

class _HalamanAlamatState extends State<HalamanAlamat> {
  final _kendali = TextEditingController();
  final _form = GlobalKey<FormState>();
  bool _menyimpan = false;

  @override
  void dispose() {
    _kendali.dispose();
    super.dispose();
  }

  Future<void> _lanjut() async {
    if (!_form.currentState!.validate()) return;
    setState(() => _menyimpan = true);
    final alamat = Konfigurasi.normalisasi(_kendali.text);
    await Konfigurasi.simpan(alamat);
    if (!mounted) return;
    Navigator.of(context).pushReplacement(
      MaterialPageRoute(builder: (_) => HalamanWeb(alamat: alamat)),
    );
  }

  @override
  Widget build(BuildContext context) {
    final tema = Theme.of(context);
    return Scaffold(
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 460),
              child: Form(
                key: _form,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Icon(Icons.campaign_rounded, size: 64, color: tema.colorScheme.primary),
                    const SizedBox(height: 12),
                    Text('Siaga Warga',
                        textAlign: TextAlign.center,
                        style: tema.textTheme.headlineMedium
                            ?.copyWith(fontWeight: FontWeight.bold, color: tema.colorScheme.primary)),
                    const SizedBox(height: 6),
                    Text(
                      'Pengaduan fasilitas umum warga RT/RW.\n'
                      'Masukkan alamat server yang diberikan pengurus.',
                      textAlign: TextAlign.center,
                      style: tema.textTheme.bodyMedium?.copyWith(color: Colors.black54),
                    ),
                    const SizedBox(height: 24),
                    TextFormField(
                      controller: _kendali,
                      keyboardType: TextInputType.url,
                      textInputAction: TextInputAction.go,
                      onFieldSubmitted: (_) => _lanjut(),
                      decoration: const InputDecoration(
                        labelText: 'Alamat server',
                        hintText: 'contoh: siaga.warga.id',
                        prefixIcon: Icon(Icons.link),
                        border: OutlineInputBorder(),
                      ),
                      validator: (nilai) {
                        if (nilai == null || nilai.trim().isEmpty) {
                          return 'Alamat server belum diisi';
                        }
                        if (nilai.trim().length < 4) return 'Alamat terlalu pendek';
                        return null;
                      },
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Boleh ditulis tanpa https:// — aplikasi menambahkannya sendiri.',
                      style: tema.textTheme.bodySmall?.copyWith(color: Colors.black54),
                    ),
                    const SizedBox(height: 20),
                    FilledButton.icon(
                      onPressed: _menyimpan ? null : _lanjut,
                      icon: _menyimpan
                          ? const SizedBox(
                              width: 18, height: 18,
                              child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                          : const Icon(Icons.arrow_forward),
                      label: Text(_menyimpan ? 'Menyiapkan…' : 'Buka Siaga Warga'),
                      style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(50)),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
