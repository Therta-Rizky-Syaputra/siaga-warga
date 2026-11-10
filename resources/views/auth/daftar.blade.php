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
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required>
                @error('email') <p class="salah">{{ $message }}</p> @enderror
            </div>
            <div class="grid-2">
                <div class="bidang">
                    <label for="rt_rw">RT / RW</label>
                    <input id="rt_rw" type="text" name="rt_rw" value="{{ old('rt_rw') }}" placeholder="RT 04 / RW 02" required>
                    @error('rt_rw') <p class="salah">{{ $message }}</p> @enderror
                </div>
                <div class="bidang">
                    <label for="telepon">Nomor telepon (opsional)</label>
                    <input id="telepon" type="text" name="telepon" value="{{ old('telepon') }}">
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
