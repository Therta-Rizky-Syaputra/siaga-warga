<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FotoLaporanController extends Controller
{
    /**
     * Foto bukti berisi rumah warga, jadi tidak boleh punya URL publik.
     * Hanya pelapor dan pengurus RT/RW yang boleh melihatnya.
     */
    public function __invoke(Request $request, Laporan $laporan): StreamedResponse
    {
        $pengguna = $request->user();

        abort_unless($pengguna->isPengelola() || $laporan->user_id === $pengguna->id, 403);
        abort_unless($laporan->foto && Storage::disk('local')->exists($laporan->foto), 404);

        return Storage::disk('local')->response($laporan->foto);
    }
}
