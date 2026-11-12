<x-layout title="Buat Laporan">
    <h1>Buat Laporan Baru</h1>
    <p class="sub">Isi data berikut. Kolom bertanda * wajib diisi.</p>

    <form method="POST" action="{{ route('laporan.simpan') }}" enctype="multipart/form-data" class="kartu">
        @csrf

        <div class="grid-2">
            <div class="bidang">
                <label for="kategori_masalah_id">Kategori masalah *</label>
                <select id="kategori_masalah_id" name="kategori_masalah_id" required>
                    <option value="">— pilih kategori —</option>
                    @foreach ($kategoris as $kategori)
                        <option value="{{ $kategori->id }}" @selected(old('kategori_masalah_id') == $kategori->id)>{{ $kategori->nama }}</option>
                    @endforeach
                </select>
                @error('kategori_masalah_id') <p class="salah">{{ $message }}</p> @enderror
            </div>
            <div class="bidang">
                <label for="prioritas">Perkiraan tingkat prioritas *</label>
                <select id="prioritas" name="prioritas" required>
                    @foreach (['rendah' => 'Rendah — tidak berbahaya', 'sedang' => 'Sedang — perlu segera', 'tinggi' => 'Tinggi — membahayakan'] as $nilai => $teks)
                        <option value="{{ $nilai }}" @selected(old('prioritas', 'sedang') === $nilai)>{{ $teks }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="bidang">
            <label for="judul">Judul laporan *</label>
            <input id="judul" type="text" name="judul" value="{{ old('judul') }}" placeholder="Contoh: Lampu jalan mati depan Blok C" required>
            @error('judul') <p class="salah">{{ $message }}</p> @enderror
        </div>

        <div class="bidang">
            <label for="deskripsi">Deskripsi masalah *</label>
            <textarea id="deskripsi" name="deskripsi" placeholder="Jelaskan kondisi, sejak kapan, dan dampaknya bagi warga." required>{{ old('deskripsi') }}</textarea>
            @error('deskripsi') <p class="salah">{{ $message }}</p> @enderror
        </div>

        <div class="bidang">
            <label for="lokasi">Lokasi acuan *</label>
            <input id="lokasi" type="text" name="lokasi" value="{{ old('lokasi') }}" placeholder="Contoh: Depan Blok C No. 12" required>
            <p class="bantuan">Tulis patokan yang mudah dikenali petugas, misalnya nomor rumah, blok, atau gang terdekat.</p>
            @error('lokasi') <p class="salah">{{ $message }}</p> @enderror
        </div>

        <div class="bidang">
            <label for="foto">Foto bukti (opsional)</label>
            <input id="foto" type="file" name="foto" accept="image/png,image/jpeg">
            <p class="bantuan">Format JPG/PNG, maksimal 2 MB. <span id="nama-foto"></span></p>
            @error('foto') <p class="salah">{{ $message }}</p> @enderror
        </div>

        <div class="baris-tombol">
            <button class="tombol" type="submit">Kirim Laporan</button>
            <a class="tombol putih" href="{{ route('laporan.index') }}">Batal</a>
        </div>
    </form>
</x-layout>
