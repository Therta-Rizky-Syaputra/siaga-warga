<x-layout title="Tangani {{ $laporan->nomorTiket() }}">
    <p class="sub"><a href="{{ route('pengurus.laporan.index') }}">&larr; Kembali ke laporan masuk</a></p>

    <div class="kartu-kepala">
        <div>
            <h1>{{ $laporan->judul }}</h1>
            <div class="meta">
                <span>{{ $laporan->nomorTiket() }}</span>
                <span>{{ $laporan->kategori->nama }}</span>
                <span>Dikirim {{ $laporan->created_at->format('d M Y H:i') }} ({{ $laporan->created_at->diffForHumans() }})</span>
            </div>
        </div>
        <div>
            <x-label-status :status="$laporan->status" />
            <x-label-prioritas :prioritas="$laporan->prioritas" />
        </div>
    </div>

    <div class="grid-2">
        <div class="kartu">
            <h2 style="margin-top:0">Detail pengaduan</h2>
            <p>{{ $laporan->deskripsi }}</p>
            <table>
                <tr><th style="width:160px">Pelapor</th><td>{{ $laporan->pelapor->name }} ({{ $laporan->pelapor->rt_rw ?? '-' }})</td></tr>
                <tr><th>Nomor HP</th><td>{{ \App\Support\Telepon::tampilkan($laporan->pelapor->telepon ?? '') ?: '—' }}</td></tr>
                <tr><th>Lokasi acuan</th><td>{{ $laporan->lokasi }}</td></tr>
                <tr><th>Ditangani pertama</th><td>{{ $laporan->ditangani_pada?->format('d M Y H:i') ?? 'Belum' }}</td></tr>
            </table>
            @if ($laporan->foto)
                <p><img class="foto-laporan" src="{{ route('laporan.foto', $laporan) }}" alt="Foto bukti laporan"></p>
            @endif
        </div>

        <div class="kartu">
            <h2 style="margin-top:0">Verifikasi &amp; ubah status</h2>
            <form method="POST" action="{{ route('pengurus.laporan.update', $laporan) }}">
                @csrf
                @method('PUT')
                <div class="bidang">
                    <label for="status">Status penanganan</label>
                    <select id="status" name="status" required>
                        @foreach (\App\Support\StatusLaporan::semua() as $s)
                            <option value="{{ $s }}" @selected($laporan->status === $s)>{{ \App\Support\StatusLaporan::label($s) }}</option>
                        @endforeach
                    </select>
                    <p class="bantuan">Status “Diproses” atau “Selesai” mencatat waktu respons pertama.</p>
                </div>
                <div class="bidang">
                    <label for="prioritas">Prioritas</label>
                    <select id="prioritas" name="prioritas" required>
                        @foreach (['rendah', 'sedang', 'tinggi'] as $p)
                            <option value="{{ $p }}" @selected($laporan->prioritas === $p)>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="bidang">
                    <label for="tanggapan">Tanggapan untuk warga (opsional)</label>
                    <textarea id="tanggapan" name="tanggapan" placeholder="Contoh: Petugas akan mengecek lokasi hari ini."></textarea>
                </div>
                <button class="tombol" type="submit" style="width:100%">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <h2>Riwayat tanggapan ({{ $laporan->tanggapans->count() }})</h2>
    <div class="kartu">
        @forelse ($laporan->tanggapans as $t)
            <div class="tanggapan {{ $t->penulis->isPengelola() ? 'milik-pengurus' : 'milik-warga' }}">
                <div class="meta">
                    <strong>{{ $t->penulis->name }}</strong>
                    <span>{{ \App\Support\Peran::label($t->penulis->role) }}</span>
                    <span>{{ $t->created_at->format('d M Y H:i') }}</span>
                    @if ($t->status_baru)
                        <span>status &rarr; {{ \App\Support\StatusLaporan::label($t->status_baru) }}</span>
                    @endif
                </div>
                <p style="margin:6px 0 0">{{ $t->isi }}</p>
            </div>
        @empty
            <p class="kosong">Belum ada tanggapan.</p>
        @endforelse
    </div>
</x-layout>
