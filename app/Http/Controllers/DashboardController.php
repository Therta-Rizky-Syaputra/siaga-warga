<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Pengumuman;
use App\Support\StatusLaporan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if ($user->isPengelola()) {
            return view('dashboard.pengurus', [
                'jumlahPerStatus' => collect(StatusLaporan::semua())
                    ->mapWithKeys(fn ($s) => [$s => Laporan::where('status', $s)->count()]),
                'total' => Laporan::count(),
                'prioritasTinggi' => Laporan::prioritasTinggi()->count(),
                'terbaru' => Laporan::with(['kategori', 'pelapor'])->latest()->take(5)->get(),
                'pengumuman' => Pengumuman::aktif()->latest()->take(3)->get(),
                'jumlahPengumuman' => Pengumuman::aktif()->count(),
            ]);
        }

        $laporans = $user->laporans();

        return view('dashboard.warga', [
            'total' => (clone $laporans)->count(),
            'belumSelesai' => (clone $laporans)
                ->whereNotIn('status', [StatusLaporan::SELESAI, StatusLaporan::DITOLAK])->count(),
            'selesai' => (clone $laporans)->where('status', StatusLaporan::SELESAI)->count(),
            'terbaru' => (clone $laporans)->with('kategori')->latest()->take(5)->get(),
            'pengumuman' => Pengumuman::aktif()->latest()->take(3)->get(),
        ]);
    }
}
