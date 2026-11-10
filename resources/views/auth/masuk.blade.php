<x-layout title="Masuk">
    <div style="max-width:420px;margin:40px auto">
        <h1>Masuk Siaga Warga</h1>
        <p class="sub">Platform pengaduan fasilitas umum warga RT/RW.</p>

        <form method="POST" action="{{ route('masuk') }}" class="kartu">
            @csrf
            <div class="bidang">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                @error('email') <p class="salah">{{ $message }}</p> @enderror
            </div>
            <div class="bidang">
                <label for="password">Kata sandi</label>
                <input id="password" type="password" name="password" required>
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
                admin@siagawarga.test &middot; pengurus@siagawarga.test &middot; warga@siagawarga.test<br>
                kata sandi: <code>password</code>
            </div>
        </div>
    </div>
</x-layout>
