<x-layout title="Kelola Pengguna">
    <h1>Kelola Basis Data Pengguna</h1>
    <p class="sub">Admin sistem menambah akun, mengubah peran, dan menghapus pengguna.</p>

    <details class="kartu">
        <summary style="cursor:pointer;font-weight:600">+ Tambah pengguna baru</summary>
        <form method="POST" action="{{ route('admin.pengguna.simpan') }}" style="margin-top:12px">
            @csrf
            <div class="grid-2">
                <div class="bidang">
                    <label for="baru-name">Nama lengkap *</label>
                    <input id="baru-name" type="text" name="name" value="{{ old('name') }}" required>
                    @error('name') <p class="salah">{{ $message }}</p> @enderror
                </div>
                <div class="bidang">
                    <label for="baru-email">Email *</label>
                    <input id="baru-email" type="email" name="email" value="{{ old('email') }}" required>
                    @error('email') <p class="salah">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid-2">
                <div class="bidang">
                    <label for="baru-role">Peran *</label>
                    <select id="baru-role" name="role" required>
                        @foreach (\App\Support\Peran::semua() as $peran)
                            <option value="{{ $peran }}" @selected(old('role') === $peran)>{{ \App\Support\Peran::label($peran) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="bidang">
                    <label for="baru-password">Kata sandi awal *</label>
                    <input id="baru-password" type="password" name="password" required>
                    @error('password') <p class="salah">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid-2">
                <div class="bidang">
                    <label for="baru-rt">RT / RW</label>
                    <input id="baru-rt" type="text" name="rt_rw" value="{{ old('rt_rw') }}">
                </div>
                <div class="bidang">
                    <label for="baru-telp">Telepon</label>
                    <input id="baru-telp" type="text" name="telepon" value="{{ old('telepon') }}">
                </div>
            </div>
            <button class="tombol" type="submit">Simpan Pengguna</button>
        </form>
    </details>

    <form method="GET" class="baris-tombol" style="margin-bottom:12px">
        <select name="peran" style="max-width:240px">
            <option value="">Semua peran</option>
            @foreach (\App\Support\Peran::semua() as $peran)
                <option value="{{ $peran }}" @selected(request('peran') === $peran)>{{ \App\Support\Peran::label($peran) }}</option>
            @endforeach
        </select>
        <button class="tombol putih" type="submit">Filter</button>
    </form>

    <div class="tabel-bungkus">
        <table>
            <thead>
                <tr><th>Nama</th><th>Email</th><th>Peran</th><th>RT/RW</th><th>Laporan</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @foreach ($penggunas as $pengguna)
                    <tr>
                        <td>
                            <form method="POST" action="{{ route('admin.pengguna.perbarui', $pengguna) }}" id="ubah-{{ $pengguna->id }}">
                                @csrf
                                @method('PUT')
                                <input type="text" name="name" value="{{ $pengguna->name }}" required>
                        </td>
                        <td><input type="email" name="email" value="{{ $pengguna->email }}" required></td>
                        <td>
                            <select name="role" required>
                                @foreach (\App\Support\Peran::semua() as $peran)
                                    <option value="{{ $peran }}" @selected($pengguna->role === $peran)>{{ \App\Support\Peran::label($peran) }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="text" name="rt_rw" value="{{ $pengguna->rt_rw }}"></td>
                        <td>{{ $pengguna->laporans_count }}</td>
                        <td>
                            <div class="baris-tombol">
                                <button class="tombol kecil" type="submit" form="ubah-{{ $pengguna->id }}">Simpan</button>
                                @unless ($pengguna->is(auth()->user()))
                                    <button class="tombol bahaya kecil" type="submit" form="hapus-{{ $pengguna->id }}"
                                            data-konfirmasi="Hapus pengguna {{ $pengguna->name }}?">Hapus</button>
                                @endunless
                            </div>
                            </form>
                            @unless ($pengguna->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.pengguna.hapus', $pengguna) }}" id="hapus-{{ $pengguna->id }}">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            @endunless
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="paginasi">{{ $penggunas->links() }}</div>
</x-layout>
