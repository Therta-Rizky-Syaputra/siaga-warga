<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Pengumuman;
use App\Support\Kanal;
use App\Support\StatusLaporan;
use Illuminate\Http\Request;

class PerubahanController extends Controller
{
    /** Ringkasan yang dipakai mode denyut (fallback) — muatannya sama dengan SSE. */
    public function __invoke(Request $request, Kanal $kanal)
    {
        return response()->json($this->ringkasan($kanal, $request))
            ->header('Cache-Control', 'no-store');
    }

    /**
     * Aliran SSE: kirim ringkasan begitu ada perubahan.
     * Dipakai layar pengurus agar laporan warga muncul tanpa refresh.
     */
    public function aliran(Request $request, Kanal $kanal)
    {
        abort_unless($request->user()?->isPengelola(), 403);

        // PENTING: lepaskan kunci sesi sebelum streaming.
        // Tanpa ini, satu koneksi SSE menahan sesi PHP sehingga pengurus tidak bisa
        // membuka laporan lain atau menyimpan perubahan sampai aliran selesai.
        $request->session()->save();

        $versiTerakhir = -1;
        $batas = now()->addMinutes(10);

        return response()->eventStream(function () use ($kanal, &$versiTerakhir, $batas, $request) {
            $pertama = true;

            while (now()->lessThan($batas)) {
                if (connection_aborted()) {
                    break;
                }

                $versi = $kanal->versi();

                if ($pertama || $versi !== $versiTerakhir) {
                    $versiTerakhir = $versi;
                    $pertama = false;

                    yield $this->ringkasan($kanal, $request);
                }

                sleep(2);
            }

            yield 'ulang';   // beri tahu klien agar menyambung ulang dengan mulus
        });
    }

    /** Angka-angka yang ditampilkan di dashboard, sesuai peran pemanggil. */
    private function ringkasan(Kanal $kanal, Request $request): array
    {
        $ringkasan = [
            'versi' => $kanal->versi(),
            'pengumuman' => Pengumuman::aktif()->count(),
            'waktu' => now()->format('H:i:s'),
        ];

        if ($request->user()?->isPengelola()) {
            return $ringkasan + [
                'total' => Laporan::count(),
                'dikirim' => Laporan::where('status', StatusLaporan::DIKIRIM)->count(),
                'prioritas_tinggi' => Laporan::prioritasTinggi()->count(),
            ];
        }

        $milik = $request->user()?->laporans();

        return $ringkasan + [
            'saya_total' => (clone $milik)->count(),
            'saya_diproses' => (clone $milik)
                ->whereNotIn('status', [StatusLaporan::SELESAI, StatusLaporan::DITOLAK])->count(),
            'saya_selesai' => (clone $milik)->where('status', StatusLaporan::SELESAI)->count(),
        ];
    }
}
