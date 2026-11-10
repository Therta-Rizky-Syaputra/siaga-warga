<x-layout :title="$laporan->judul">
    @php($urutan = \App\Support\StatusLaporan::urutan())
    @php($posisi = array_search($laporan->status, $urutan, true))
    @php($pengelola = auth()->user()->isPengelola())

    <p class="sub"><a href="{{ $pengelola ? route('pengurus.laporan.index') : route('laporan.index') }}">&larr; Kembali ke daftar laporan</a></p>

    <div class="kartu-kepala">
        <div>
            <h1>{{ $laporan->judul }}</h1>
            <div class="meta">
                <span>{{ $laporan->nomorTiket() }}</span>
                <span>{{ $laporan->kategori->nama }}</span>
                <span>Dilaporkan {{ $laporan->created_at->format('d M Y H:i') }}</span>
                <span>Pelapor: {{ $laporan->pelapor->name }}</span>
            </div>
        </div>
        <div>
            <x-label-status :status="$laporan->status" />
            <x-label-prioritas :prioritas="$laporan->prioritas" />
        </div>
    </div>

    @if ($laporan->status === \App\Support\StatusLaporan::DITOLAK)
        <div class="pesan gagal">Laporan ini ditolak. Lihat alasan pada tanggapan di bawah.</div>
    @else
        <div class="linimasa">
            @foreach ($urutan as $i => $tahap)
                <div class="tahap {{ $posisi !== false && $i <= $posisi ? 'lewat' : '' }}">
                    {{ \App\Support\StatusLaporan::label($tahap) }}
                </div>
            @endforeach
        </div>
    @endif

    <div class="kartu">
        <h2 style="margin-top:0">Detail laporan</h2>
        <p>{{ $laporan->deskripsi }}</p>
        <table>
            <tr><th style="width:180px">Lokasi acuan</th><td>{{ $laporan->lokasi }}</td></tr>
            <tr><th>Waktu penanganan pertama</th><td>{{ $laporan->ditangani_pada?->format('d M Y H:i') ?? 'Belum ditangani' }}</td></tr>
            <tr><th>Penanggung jawab</th><td>{{ $laporan->penanggungJawab->name ?? 'Belum ditetapkan' }}</td></tr>
        </table>
        @if ($laporan->foto)
            <p><img class="foto-laporan" src="{{ Storage::url($laporan->foto) }}" alt="Foto bukti laporan {{ $laporan->nomorTiket() }}"></p>
        @else
            <p class="kosong">Warga tidak melampirkan foto.</p>
        @endif
    </div>

    @if ($pengelola)
        <div class="baris-tombol" style="margin-bottom:14px">
            <a class="tombol" href="{{ route('pengurus.laporan.show', $laporan) }}">Verifikasi &amp; Ubah Status</a>
        </div>
    @endif

    <h2>Tanggapan &amp; riwayat ({{ $laporan->tanggapans->count() }})</h2>
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

        <form method="POST" action="{{ route('laporan.tanggapi', $laporan) }}" style="margin-top:14px">
            @csrf
            <div class="bidang">
                <label for="isi">Tambah tanggapan</label>
                <textarea id="isi" name="isi" placeholder="Tulis pertanyaan atau informasi tambahan untuk pengurus." required>{{ old('isi') }}</textarea>
                @error('isi') <p class="salah">{{ $message }}</p> @enderror
            </div>
            <button class="tombol" type="submit">Kirim Tanggapan</button>
        </form>
    </div>
</x-layout>
