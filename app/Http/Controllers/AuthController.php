<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Peran;
use App\Support\Telepon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function formMasuk()
    {
        return view('auth.masuk');
    }

    public function masuk(Request $request)
    {
        $data = $request->validate([
            'telepon' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string'],
        ]);

        $kunci = 'masuk:'.Telepon::normalisasi($data['telepon']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($kunci, 5)) {
            throw ValidationException::withMessages([
                'telepon' => 'Terlalu banyak percobaan. Coba lagi dalam '
                    .RateLimiter::availableIn($kunci).' detik.',
            ]);
        }

        if (! Auth::attempt([
            'telepon' => Telepon::normalisasi($data['telepon']),
            'password' => $data['password'],
        ], $request->boolean('ingat_saya'))) {
            RateLimiter::hit($kunci, 60);

            throw ValidationException::withMessages([
                'telepon' => 'Nomor HP atau kata sandi tidak cocok.',
            ]);
        }

        RateLimiter::clear($kunci);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function formDaftar()
    {
        return view('auth.daftar');
    }

    public function daftar(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'telepon' => ['required', 'string', 'max:20'],
            'rt_rw' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:120', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [], ['telepon' => 'nomor HP']);

        $data['telepon'] = Telepon::normalisasi($data['telepon']);

        if (User::where('telepon', $data['telepon'])->exists()) {
            throw ValidationException::withMessages([
                'telepon' => 'Nomor HP ini sudah terdaftar. Silakan masuk, atau hubungi pengurus RT/RW.',
            ]);
        }

        $data['role'] = Peran::WARGA;
        $user = User::create($data);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('sukses', 'Akun warga berhasil dibuat. Selamat datang!');
    }

    public function keluar(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('masuk');
    }
}
