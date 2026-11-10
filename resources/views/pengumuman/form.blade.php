<x-layout :title="$pengumuman->exists ? 'Ubah Pengumuman' : 'Pengumuman Baru'">
    <h1>{{ $pengumuman->exists ? 'Ubah Pengumuman' : 'Pengumuman Baru' }}</h1>
    <p class="sub">Pengumuman berlaku untuk seluruh warga dan tampil di dashboard mereka.</p>

    <form method="POST" action="{{ $pengumuman->exists ? route('pengumuman.perbarui', $pengumuman) : route('pengumuman.simpan') }}" class="kartu">
        @csrf
        @if ($pengumuman->exists)
            @method('PUT')
        @endif

        <div class="bidang">
            <label for="judul">Judul *</label>
            <input id="judul" type="text" name="judul" value="{{ old('judul', $pengumuman->judul) }}" placeholder="Contoh: Kerja Bakti Minggu Pagi" required>
            @error('judul') <p class="salah">{{ $message }}</p> @enderror
        </div>

        <div class="bidang">
            <label for="isi">Isi pengumuman *</label>
            <textarea id="isi" name="isi" style="min-height:180px" required>{{ old('isi', $pengumuman->isi) }}</textarea>
            @error('isi') <p class="salah">{{ $message }}</p> @enderror
        </div>

        <div class="bidang">
            <label for="berlaku_sampai">Berlaku sampai (opsional)</label>
            <input id="berlaku_sampai" type="datetime-local" name="berlaku_sampai"
                   value="{{ old('berlaku_sampai', $pengumuman->berlaku_sampai?->format('Y-m-d\TH:i')) }}">
            <p class="bantuan">Kosongkan bila pengumuman berlaku tanpa batas waktu.</p>
            @error('berlaku_sampai') <p class="salah">{{ $message }}</p> @enderror
        </div>

        <div class="baris-tombol">
            <button class="tombol" type="submit">Simpan</button>
            <a class="tombol putih" href="{{ route('pengumuman.index') }}">Batal</a>
        </div>
    </form>
</x-layout>
