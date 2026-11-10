<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index()
    {
        return view('pengumuman.index', [
            'pengumumans' => Pengumuman::with('penulis')->latest()->paginate(10),
        ]);
    }

    public function buat()
    {
        return view('pengumuman.form', ['pengumuman' => new Pengumuman]);
    }

    public function simpan(Request $request)
    {
        $pengumuman = $request->user()->pengumumans()->create($this->data($request));

        return redirect()->route('pengumuman.show', $pengumuman)->with('sukses', 'Pengumuman dipublikasikan.');
    }

    public function show(Pengumuman $pengumuman)
    {
        return view('pengumuman.show', ['pengumuman' => $pengumuman->load('penulis')]);
    }

    public function ubah(Pengumuman $pengumuman)
    {
        return view('pengumuman.form', compact('pengumuman'));
    }

    public function perbarui(Request $request, Pengumuman $pengumuman)
    {
        $pengumuman->update($this->data($request));

        return redirect()->route('pengumuman.show', $pengumuman)->with('sukses', 'Pengumuman diperbarui.');
    }

    public function hapus(Pengumuman $pengumuman)
    {
        $pengumuman->delete();

        return redirect()->route('pengumuman.index')->with('sukses', 'Pengumuman dihapus.');
    }

    private function data(Request $request): array
    {
        return $request->validate([
            'judul' => ['required', 'string', 'max:140'],
            'isi' => ['required', 'string', 'max:5000'],
            'berlaku_sampai' => ['nullable', 'date', 'after:now'],
        ]);
    }
}
