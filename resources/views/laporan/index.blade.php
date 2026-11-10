<x-layout title="Laporan Saya">
    <div class="kartu-kepala">
        <div>
            <h1>Laporan Saya</h1>
            <p class="sub">Riwayat pengaduan yang Anda kirim beserta status terkininya.</p>
        </div>
        <a class="tombol" href="{{ route('laporan.buat') }}">+ Buat Laporan</a>
    </div>

    <form method="GET" class="baris-tombol" style="margin-bottom:12px">
        <select name="status" style="max-width:240px">
            <option value="">Semua status</option>
            @foreach (\App\Support\StatusLaporan::semua() as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ \App\Support\StatusLaporan::label($s) }}</option>
            @endforeach
        </select>
        <button class="tombol putih" type="submit">Filter</button>
        @if (request('status'))
            <a class="tombol putih kecil" href="{{ route('laporan.index') }}">Reset</a>
        @endif
    </form>

    <div class="kartu">
        <ul class="daftar">
            @forelse ($laporans as $laporan)
                <li>
                    <div class="kartu-kepala">
                        <div>
                            <a class="judul-item" href="{{ route('laporan.show', $laporan) }}">{{ $laporan->judul }}</a>
                            <div class="meta">
                                <span>{{ $laporan->nomorTiket() }}</span>
                                <span>{{ $laporan->kategori->nama }}</span>
                                <span>{{ $laporan->lokasi }}</span>
                                <span>Dikirim {{ $laporan->created_at->format('d M Y H:i') }}</span>
                                <span>{{ $laporan->tanggapans->count() }} tanggapan</span>
                            </div>
                        </div>
                        <div>
                            <x-label-status :status="$laporan->status" />
                            <x-label-prioritas :prioritas="$laporan->prioritas" />
                        </div>
                    </div>
                </li>
            @empty
                <li class="kosong">Belum ada laporan pada filter ini.</li>
            @endforelse
        </ul>
    </div>

    <div class="paginasi">{{ $laporans->links() }}</div>
</x-layout>
