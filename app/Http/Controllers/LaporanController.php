<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanLaporanRequest;
use App\Models\KategoriMasalah;
use App\Models\Laporan;
use App\Support\Kanal;
use App\Support\StatusLaporan;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $laporans = $request->user()->laporans()
            ->with('kategori')
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('laporan.index', compact('laporans'));
    }

    public function buat()
    {
        return view('laporan.form', [
            'kategoris' => KategoriMasalah::orderBy('nama')->get(),
            'laporan' => new Laporan(['prioritas' => 'sedang']),
        ]);
    }

    public function simpan(SimpanLaporanRequest $request)
    {
        $data = $request->validated();
        // disk "local" = privat: foto rumah warga tidak boleh punya URL publik
        $data['foto'] = $request->file('foto')?->store('laporan', 'local');
        $data['status'] = StatusLaporan::DIKIRIM;

        $laporan = $request->user()->laporans()->create($data);

        app(Kanal::class)->tandaiBerubah();

        return redirect()->route('laporan.show', $laporan)
            ->with('sukses', 'Laporan '.$laporan->nomorTiket().' terkirim. Pantau statusnya di sini.');
    }

    public function show(Request $request, Laporan $laporan)
    {
        $this->pastikanPunyaAkses($request, $laporan);

        return view('laporan.show', [
            'laporan' => $laporan->load(['kategori', 'tanggapans.penulis', 'pelapor', 'penanggungJawab']),
        ]);
    }

    public function tanggapi(Request $request, Laporan $laporan)
    {
        $this->pastikanPunyaAkses($request, $laporan);

        $data = $request->validate(['isi' => ['required', 'string', 'max:1000']]);

        $laporan->tanggapans()->create($data + ['user_id' => $request->user()->id]);

        return back()->with('sukses', 'Tanggapan terkirim.');
    }

    /** Warga hanya boleh melihat & menanggapi laporan miliknya sendiri. */
    private function pastikanPunyaAkses(Request $request, Laporan $laporan): void
    {
        abort_unless($request->user()->isPengelola() || $laporan->user_id === $request->user()->id, 403);
    }
}
