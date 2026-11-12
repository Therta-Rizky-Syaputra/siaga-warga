<x-layout title="Daftar Warga">
    <div style="max-width:520px;margin:32px auto">
        <h1>Daftar Akun Warga</h1>
        <p class="sub">Akun warga dipakai untuk mengirim laporan dan memantau statusnya.</p>

        <form method="POST" action="{{ route('daftar') }}" class="kartu">
            @csrf
            <div class="bidang">
                <label for="name">Nama lengkap</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
                @error('name') <p class="salah">{{ $message }}</p> @enderror
            </div>
            <div class="bidang">
                <label for="telepon">Nomor HP *</label>
                <input id="telepon" type="tel" inputmode="tel" name="telepon" value="{{ old('telepon') }}"
                       placeholder="0812-3456-7890" autocomplete="username" required>
                <p class="bantuan">Nomor ini dipakai untuk masuk. Boleh ditulis 0812…, 62812…, atau +62 812…</p>
                @error('telepon') <p class="salah">{{ $message }}</p> @enderror
            </div>
            <div class="grid-2">
                <div class="bidang">
                    <label for="rt_rw">RT / RW</label>
                    <input id="rt_rw" type="text" name="rt_rw" value="{{ old('rt_rw') }}" placeholder="RT 04 / RW 02" required>
                    @error('rt_rw') <p class="salah">{{ $message }}</p> @enderror
                </div>
                <div class="bidang">
                    <label for="email">Email (opsional)</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="boleh dikosongkan">
                    @error('email') <p class="salah">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid-2">
                <div class="bidang">
                    <label for="password">Kata sandi</label>
                    <input id="password" type="password" name="password" required>
                    <p class="bantuan">Minimal 8 karakter.</p>
                </div>
                <div class="bidang">
                    <label for="password_confirmation">Ulangi kata sandi</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required>
                </div>
            </div>
            @error('password') <p class="salah">{{ $message }}</p> @enderror

            <button class="tombol" type="submit" style="width:100%">Daftar</button>
        </form>

        <p class="sub">Sudah punya akun? <a href="{{ route('masuk') }}">Masuk</a></p>
    </div>
</x-layout>
