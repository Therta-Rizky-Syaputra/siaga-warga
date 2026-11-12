<x-layout title="Masuk">
    <div style="max-width:420px;margin:40px auto">
        <h1>Masuk Siaga Warga</h1>
        <p class="sub">Platform pengaduan fasilitas umum warga RT/RW.</p>

        <form method="POST" action="{{ route('masuk') }}" class="kartu">
            @csrf
            <div class="bidang">
                <label for="telepon">Nomor HP</label>
                <input id="telepon" type="tel" inputmode="tel" name="telepon" value="{{ old('telepon') }}"
                       placeholder="0812-3456-7890" autocomplete="username" required autofocus>
                @error('telepon') <p class="salah">{{ $message }}</p> @enderror
            </div>
            <div class="bidang">
                <label for="password">Kata sandi</label>
                <input id="password" type="password" name="password" autocomplete="current-password" required>
            </div>
            <div class="bidang">
                <label style="font-weight:400">
                    <input type="checkbox" name="ingat_saya" value="1" style="width:auto;min-height:auto"> Ingat saya
                </label>
            </div>
            <button class="tombol" type="submit" style="width:100%">Masuk</button>
        </form>

        <p class="sub">Belum punya akun? <a href="{{ route('daftar') }}">Daftar sebagai warga</a></p>
        <div class="kartu" style="font-size:14px">
            <strong>Akun demo</strong>
            <div class="meta" style="display:block">
                0812-0000-0001 (admin) &middot; 0812-0000-0002 (pengurus) &middot; 0812-0000-0003 (warga)<br>
                kata sandi: <code>password</code>
            </div>
        </div>
    </div>
</x-layout>
