<x-layout title="Dashboard Pengurus">
    <div class="kartu-kepala">
        <div>
            <h1>Dashboard Pengurus RT/RW</h1>
            <p class="sub">Ringkasan pengaduan warga berdasarkan prioritas dan status penanganan.</p>
        </div>
        <div class="baris-tombol">
            <a class="tombol" href="{{ route('pengurus.laporan.index') }}">Kelola Laporan Masuk</a>
            <a class="tombol putih" href="{{ route('pengumuman.buat') }}">+ Pengumuman</a>
        </div>
    </div>

    <div class="petak">
        <div class="kartu"><div class="angka">{{ $total }}</div><div class="label">Total laporan</div></div>
        <div class="kartu"><div class="angka" style="color:var(--merah)">{{ $prioritasTinggi }}</div><div class="label">Prioritas tinggi belum selesai</div></div>
        @foreach (['dikirim' => 'Menunggu verifikasi', 'diverifikasi' => 'Diverifikasi', 'diproses' => 'Diproses', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'] as $status => $label)
            <div class="kartu"><div class="angka">{{ $jumlahPerStatus[$status] }}</div><div class="label">{{ $label }}</div></div>
        @endforeach
    </div>

    <h2>Laporan terbaru</h2>
    <div class="tabel-bungkus">
        <table>
            <thead>
                <tr><th>Tiket</th><th>Judul</th><th>Pelapor</th><th>Status</th><th>Prioritas</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($terbaru as $laporan)
                    <tr>
                        <td>{{ $laporan->nomorTiket() }}</td>
                        <td>{{ $laporan->judul }}<div class="meta"><span>{{ $laporan->lokasi }}</span></div></td>
                        <td>{{ $laporan->pelapor->name }}</td>
                        <td><x-label-status :status="$laporan->status" /></td>
                        <td><x-label-prioritas :prioritas="$laporan->prioritas" /></td>
                        <td><a class="tombol putih kecil" href="{{ route('pengurus.laporan.show', $laporan) }}">Tangani</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="kosong">Belum ada laporan warga.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <h2>Pengumuman aktif</h2>
    <div class="kartu">
        @forelse ($pengumuman as $p)
            <div style="border-bottom:1px solid var(--garis);padding:8px 0">
                <a class="judul-item" href="{{ route('pengumuman.show', $p) }}">{{ $p->judul }}</a>
                <div class="meta"><span>{{ $p->created_at->format('d M Y') }}</span></div>
            </div>
        @empty
            <p class="kosong">Belum ada pengumuman aktif.</p>
        @endforelse
    </div>
</x-layout>
