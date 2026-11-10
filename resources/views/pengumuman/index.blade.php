<x-layout title="Pengumuman">
    <div class="kartu-kepala">
        <div>
            <h1>Pengumuman RT/RW</h1>
            <p class="sub">Informasi resmi dari pengurus, misalnya jadwal kerja bakti.</p>
        </div>
        @if (auth()->user()->isPengelola())
            <a class="tombol" href="{{ route('pengumuman.buat') }}">+ Pengumuman</a>
        @endif
    </div>

    <div class="kartu">
        <ul class="daftar">
            @forelse ($pengumumans as $p)
                <li>
                    <a class="judul-item" href="{{ route('pengumuman.show', $p) }}">{{ $p->judul }}</a>
                    <div class="meta">
                        <span>{{ $p->penulis->name }}</span>
                        <span>{{ $p->created_at->format('d M Y H:i') }}</span>
                        <span>{{ $p->berlaku_sampai ? 'Berlaku s.d. '.$p->berlaku_sampai->format('d M Y') : 'Tanpa batas waktu' }}</span>
                    </div>
                    <p style="margin:6px 0 0;color:#334155">{{ Str::limit($p->isi, 160) }}</p>
                </li>
            @empty
                <li class="kosong">Belum ada pengumuman.</li>
            @endforelse
        </ul>
    </div>

    <div class="paginasi">{{ $pengumumans->links() }}</div>
</x-layout>
