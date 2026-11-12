@props(['title' => 'Siaga Warga'])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1d4ed8">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/ikon/ikon-192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="SiagaWarga">
    <title>{{ $title }} — Siaga Warga</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@auth
    {{-- papan pantau perubahan; tombol pasang muncul otomatis lewat JS --}}
    <div data-pantau
         data-denyut="{{ route('perubahan') }}"
         @if (auth()->user()->isPengelola()) data-aliran="{{ route('perubahan.aliran') }}" @endif
         hidden></div>
@endauth

<header class="topbar">
    <div class="topbar-inner">
        <a href="{{ route('dashboard') }}" class="brand">Siaga<span>Warga</span></a>

        @auth
            @php($user = auth()->user())
            <nav class="nav">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'aktif' : '' }}">Dashboard</a>
                <a href="{{ route('laporan.index') }}" class="{{ request()->routeIs('laporan.*') ? 'aktif' : '' }}">
                    {{ $user->isWarga() ? 'Laporan Saya' : 'Laporan Warga' }}
                </a>
                @if ($user->isPengelola())
                    <a href="{{ route('pengurus.laporan.index') }}" class="{{ request()->routeIs('pengurus.*') ? 'aktif' : '' }}">Laporan Masuk</a>
                @endif
                <a href="{{ route('pengumuman.index') }}" class="{{ request()->routeIs('pengumuman.*') ? 'aktif' : '' }}">Pengumuman</a>
                @if ($user->isAdmin())
                    <a href="{{ route('admin.pengguna') }}" class="{{ request()->routeIs('admin.*') ? 'aktif' : '' }}">Kelola Pengguna</a>
                @endif
            </nav>
            <div class="who">
                {{ $user->name }}<br>
                <strong>{{ \App\Support\Peran::label($user->role) }}</strong>
                <form method="POST" action="{{ route('keluar') }}" style="margin-top:4px">
                    @csrf
                    <button class="tombol putih kecil" type="submit">Keluar</button>
                    <button class="tombol kecil" type="button" id="pasang-ke-hp" hidden>Pasang di HP</button>
                </form>
            </div>
        @endauth
    </div>
</header>

<main class="wadah">
    @if (session('sukses'))
        <div class="pesan sukses">{{ session('sukses') }}</div>
    @endif
    @if (session('gagal'))
        <div class="pesan gagal">{{ session('gagal') }}</div>
    @endif

    @auth
        <div id="tawaran-muat" class="pesan informasi" hidden>
            Ada laporan baru masuk.
            <button class="tombol kecil" type="button" onclick="location.reload()">Muat ulang</button>
        </div>
    @endauth

    {{ $slot }}
</main>
</body>
</html>
