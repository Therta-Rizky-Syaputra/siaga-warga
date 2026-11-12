<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\KategoriMasalah;
use App\Models\Laporan;
use App\Support\Kanal;
use App\Support\StatusLaporan;
use Illuminate\Http\Request;

class LaporanMasukController extends Controller
{
    public function index(Request $request)
    {
        $laporans = Laporan::with(['kategori', 'pelapor'])
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->when($request->integer('kategori'), fn ($q, $k) => $q->where('kategori_masalah_id', $k))
            ->when($request->string('q')->toString(), fn ($q, $cari) => $q->where(fn ($q) => $q
                ->where('judul', 'like', "%{$cari}%")
                ->orWhere('lokasi', 'like', "%{$cari}%")))
            // prioritas tinggi lebih dulu, lalu laporan paling lama menunggu
            ->orderByRaw("case prioritas when 'tinggi' then 1 when 'sedang' then 2 else 3 end")
            ->oldest()
            ->paginate(10)
            ->withQueryString();

        return view('pengurus.laporan.index', [
            'laporans' => $laporans,
            'kategoris' => KategoriMasalah::orderBy('nama')->get(),
            'filter' => $request->only('status', 'kategori', 'q'),
        ]);
    }

    public function show(Laporan $laporan)
    {
        return view('pengurus.laporan.show', [
            'laporan' => $laporan->load(['kategori', 'pelapor', 'tanggapans.penulis', 'penanggungJawab']),
        ]);
    }

    public function update(Request $request, Laporan $laporan)
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', StatusLaporan::semua())],
            'prioritas' => ['required', 'in:rendah,sedang,tinggi'],
            'tanggapan' => ['nullable', 'string', 'max:1000'],
        ]);

        $sebelumnya = $laporan->status;

        $laporan->update([
            'status' => $data['status'],
            'prioritas' => $data['prioritas'],
            // pencatat pertama kali laporan masuk tahap penanganan (metrik waktu respons)
            'ditangani_oleh' => $laporan->ditangani_oleh ?? $request->user()->id,
            'ditangani_pada' => $laporan->ditangani_pada ?? (in_array($data['status'], [StatusLaporan::DIPROSES, StatusLaporan::SELESAI], true) ? now() : null),
        ]);

        if (filled($data['tanggapan'])) {
            $laporan->tanggapans()->create([
                'user_id' => $request->user()->id,
                'isi' => $data['tanggapan'],
                'status_baru' => $sebelumnya === $data['status'] ? null : $data['status'],
            ]);
        }

        app(Kanal::class)->tandaiBerubah();

        return back()->with('sukses', 'Laporan '.$laporan->nomorTiket().' diperbarui.');
    }
}
