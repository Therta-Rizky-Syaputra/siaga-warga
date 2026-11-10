<x-layout :title="$pengumuman->judul">
    <p class="sub"><a href="{{ route('pengumuman.index') }}">&larr; Semua pengumuman</a></p>

    <div class="kartu">
        <h1>{{ $pengumuman->judul }}</h1>
        <div class="meta">
            <span>{{ $pengumuman->penulis->name }}</span>
            <span>{{ $pengumuman->created_at->format('d M Y H:i') }}</span>
            <span>{{ $pengumuman->berlaku_sampai ? 'Berlaku s.d. '.$pengumuman->berlaku_sampai->format('d M Y') : 'Tanpa batas waktu' }}</span>
        </div>
        <p style="white-space:pre-line">{{ $pengumuman->isi }}</p>

        @if (auth()->user()->isPengelola())
            <div class="baris-tombol">
                <a class="tombol putih" href="{{ route('pengumuman.ubah', $pengumuman) }}">Ubah</a>
                <form method="POST" action="{{ route('pengumuman.hapus', $pengumuman) }}">
                    @csrf
                    @method('DELETE')
                    <button class="tombol bahaya" type="submit" data-konfirmasi="Hapus pengumuman ini?">Hapus</button>
                </form>
            </div>
        @endif
    </div>
</x-layout>
