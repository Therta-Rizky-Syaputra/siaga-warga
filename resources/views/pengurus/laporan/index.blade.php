<x-layout title="Laporan Masuk">
    <h1>Laporan Masuk</h1>
    <p class="sub">Diurutkan otomatis: prioritas tinggi lebih dulu, lalu laporan yang paling lama menunggu.</p>

    <form method="GET" class="kartu baris-tombol">
        <input type="text" name="q" value="{{ $filter['q'] ?? '' }}" placeholder="Cari judul atau lokasi" style="flex:2;min-width:180px">
        <select name="status" style="flex:1;min-width:150px">
            <option value="">Semua status</option>
            @foreach (\App\Support\StatusLaporan::semua() as $s)
                <option value="{{ $s }}" @selected(($filter['status'] ?? '') === $s)>{{ \App\Support\StatusLaporan::label($s) }}</option>
            @endforeach
        </select>
        <select name="kategori" style="flex:1;min-width:150px">
            <option value="">Semua kategori</option>
            @foreach ($kategoris as $k)
                <option value="{{ $k->id }}" @selected(($filter['kategori'] ?? '') == $k->id)>{{ $k->nama }}</option>
            @endforeach
        </select>
        <button class="tombol" type="submit">Terapkan</button>
    </form>

    <div class="tabel-bungkus">
        <table>
            <thead>
                <tr><th>Tiket</th><th>Judul &amp; lokasi</th><th>Kategori</th><th>Pelapor</th><th>Status</th><th>Prioritas</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($laporans as $laporan)
                    <tr>
                        <td>{{ $laporan->nomorTiket() }}<div class="meta"><span>{{ $laporan->created_at->diffForHumans() }}</span></div></td>
                        <td>{{ $laporan->judul }}<div class="meta"><span>{{ $laporan->lokasi }}</span></div></td>
                        <td>{{ $laporan->kategori->nama }}</td>
                        <td>{{ $laporan->pelapor->name }}</td>
                        <td><x-label-status :status="$laporan->status" /></td>
                        <td><x-label-prioritas :prioritas="$laporan->prioritas" /></td>
                        <td><a class="tombol kecil" href="{{ route('pengurus.laporan.show', $laporan) }}">Tangani</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="kosong">Tidak ada laporan yang cocok dengan filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="paginasi">{{ $laporans->links() }}</div>
</x-layout>
