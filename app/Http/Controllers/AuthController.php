<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Peran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $kredensial = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($kredensial, $request->boolean('ingat_saya'))) {
            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi tidak cocok.',
            ]);
        }

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
            'email' => ['required', 'email', 'max:120', 'unique:users,email'],
            'rt_rw' => ['required', 'string', 'max:40'],
            'telepon' => ['nullable', 'string', 'max:20'],
            // ponytail: password rule bawaan Laravel, tidak ada konfigurasi tambahan
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

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
