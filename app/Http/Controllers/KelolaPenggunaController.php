<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Peran;
use App\Support\Telepon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class KelolaPenggunaController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.pengguna', [
            'penggunas' => User::withCount('laporans')
                ->when($request->string('peran')->toString(), fn ($q, $p) => $q->where('role', $p))
                ->orderBy('role')->orderBy('name')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function simpan(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'telepon' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:120', 'unique:users,email'],
            'role' => ['required', Rule::in(Peran::semua())],
            'rt_rw' => ['nullable', 'string', 'max:40'],
            'password' => ['required', Password::defaults()],
        ], [], ['telepon' => 'nomor HP']);

        $data['telepon'] = Telepon::normalisasi($data['telepon']);

        if (User::where('telepon', $data['telepon'])->exists()) {
            return back()->with('gagal', 'Nomor HP itu sudah dipakai pengguna lain.');
        }

        User::create($data);

        return back()->with('sukses', 'Pengguna baru ditambahkan.');
    }

    public function perbarui(Request $request, User $pengguna)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'telepon' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:120', Rule::unique('users', 'email')->ignore($pengguna->id)],
            'role' => ['required', Rule::in(Peran::semua())],
            'rt_rw' => ['nullable', 'string', 'max:40'],
            'password' => ['nullable', Password::defaults()],
        ], [], ['telepon' => 'nomor HP']);

        $data['telepon'] = Telepon::normalisasi($data['telepon']);

        if (User::where('telepon', $data['telepon'])->whereKeyNot($pengguna->id)->exists()) {
            return back()->with('gagal', 'Nomor HP itu sudah dipakai pengguna lain.');
        }

        if (blank($data['password'])) {
            unset($data['password']);
        }

        // cegah admin terakhir dikunci di luar sistem
        if ($pengguna->isAdmin() && $data['role'] !== Peran::ADMIN
            && User::where('role', Peran::ADMIN)->count() === 1) {
            return back()->with('gagal', 'Tidak bisa menurunkan peran admin terakhir.');
        }

        $pengguna->update($data);

        return back()->with('sukses', 'Data pengguna diperbarui.');
    }

    public function hapus(Request $request, User $pengguna)
    {
        if ($pengguna->is($request->user())) {
            return back()->with('gagal', 'Tidak bisa menghapus akun yang sedang dipakai.');
        }

        $pengguna->delete();

        return back()->with('sukses', 'Pengguna dihapus.');
    }
}
