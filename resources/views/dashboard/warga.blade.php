<x-layout title="Dashboard Warga">
    <div class="kartu-kepala">
        <div>
            <h1>Halo, {{ auth()->user()->name }}</h1>
            <p class="sub">Kirim laporan fasilitas umum yang rusak dan pantau progresnya di sini.</p>
        </div>
        <a class="tombol" href="{{ route('laporan.buat') }}">+ Buat Laporan</a>
    </div>

    <div class="petak">
        <div class="kartu"><div class="angka">{{ $total }}</div><div class="label">Total laporan saya</div></div>
        <div class="kartu"><div class="angka">{{ $belumSelesai }}</div><div class="label">Sedang ditangani</div></div>
        <div class="kartu"><div class="angka">{{ $selesai }}</div><div class="label">Selesai</div></div>
    </div>

    <h2>Laporan terbaru</h2>
    <div class="kartu">
        @forelse ($terbaru as $laporan)
            <div style="border-bottom:1px solid var(--garis);padding:10px 0">
                <a class="judul-item" href="{{ route('laporan.show', $laporan) }}">{{ $laporan->judul }}</a>
                <div class="meta">
                    <span>{{ $laporan->nomorTiket() }}</span>
                    <span>{{ $laporan->kategori->nama }}</span>
                    <span>{{ $laporan->lokasi }}</span>
                    <span>{{ $laporan->created_at->diffForHumans() }}</span>
                </div>
                <div style="margin-top:6px">
                    <x-label-status :status="$laporan->status" />
                    <x-label-prioritas :prioritas="$laporan->prioritas" />
                </div>
            </div>
        @empty
            <p class="kosong">Belum ada laporan. Klik “Buat Laporan” untuk mengirim laporan pertama.</p>
        @endforelse
    </div>

    <h2>Pengumuman terbaru</h2>
    <div class="kartu">
        @forelse ($pengumuman as $p)
            <div style="border-bottom:1px solid var(--garis);padding:8px 0">
                <a class="judul-item" href="{{ route('pengumuman.show', $p) }}">{{ $p->judul }}</a>
                <div class="meta"><span>{{ $p->penulis->name }}</span><span>{{ $p->created_at->format('d M Y') }}</span></div>
            </div>
        @empty
            <p class="kosong">Belum ada pengumuman.</p>
        @endforelse
    </div>
</x-layout>
