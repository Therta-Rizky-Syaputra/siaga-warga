<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KelolaPenggunaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PengumumanController;
use App\Http\Controllers\Pengurus\LaporanMasukController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route(auth()->check() ? 'dashboard' : 'masuk'));

// ---------- Tamu ----------
Route::middleware('guest')->group(function () {
    Route::get('/masuk', [AuthController::class, 'formMasuk'])->name('masuk');
    Route::post('/masuk', [AuthController::class, 'masuk']);
    Route::get('/daftar', [AuthController::class, 'formDaftar'])->name('daftar');
    Route::post('/daftar', [AuthController::class, 'daftar']);
});

// ---------- Semua pengguna terautentikasi ----------
Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthController::class, 'keluar'])->name('keluar');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // Warga: melapor & memantau progres pengaduan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/baru', [LaporanController::class, 'buat'])->name('laporan.buat');
    Route::post('/laporan', [LaporanController::class, 'simpan'])->name('laporan.simpan');
    Route::get('/laporan/{laporan}', [LaporanController::class, 'show'])->name('laporan.show');
    Route::post('/laporan/{laporan}/tanggapan', [LaporanController::class, 'tanggapi'])->name('laporan.tanggapi');

    // Pengumuman: semua boleh baca
    Route::get('/pengumuman', [PengumumanController::class, 'index'])->name('pengumuman.index');

    Route::middleware('peran:pengurus,admin')->group(function () {
        // Pengurus RT/RW: verifikasi, respons, prioritas, pengumuman
        Route::get('/pengumuman/baru', [PengumumanController::class, 'buat'])->name('pengumuman.buat');
        Route::post('/pengumuman', [PengumumanController::class, 'simpan'])->name('pengumuman.simpan');
        Route::get('/pengumuman/{pengumuman}/ubah', [PengumumanController::class, 'ubah'])->name('pengumuman.ubah');
        Route::put('/pengumuman/{pengumuman}', [PengumumanController::class, 'perbarui'])->name('pengumuman.perbarui');
        Route::delete('/pengumuman/{pengumuman}', [PengumumanController::class, 'hapus'])->name('pengumuman.hapus');

        Route::get('/masuk/laporan', [LaporanMasukController::class, 'index'])->name('pengurus.laporan.index');
        Route::get('/masuk/laporan/{laporan}', [LaporanMasukController::class, 'show'])->name('pengurus.laporan.show');
        Route::put('/masuk/laporan/{laporan}', [LaporanMasukController::class, 'update'])->name('pengurus.laporan.update');
    });

    Route::get('/pengumuman/{pengumuman}', [PengumumanController::class, 'show'])->name('pengumuman.show');

    // Admin sistem: kelola basis data pengguna
    Route::middleware('peran:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/pengguna', [KelolaPenggunaController::class, 'index'])->name('pengguna');
        Route::post('/pengguna', [KelolaPenggunaController::class, 'simpan'])->name('pengguna.simpan');
        Route::put('/pengguna/{pengguna}', [KelolaPenggunaController::class, 'perbarui'])->name('pengguna.perbarui');
        Route::delete('/pengguna/{pengguna}', [KelolaPenggunaController::class, 'hapus'])->name('pengguna.hapus');
    });
});
