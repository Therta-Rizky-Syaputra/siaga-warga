<?php

namespace Tests\Feature;

use App\Models\KategoriMasalah;
use App\Models\Laporan;
use App\Models\User;
use App\Support\Peran;
use App\Support\StatusLaporan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlurLaporanTest extends TestCase
{
    use RefreshDatabase;

    private function warga(): User
    {
        return User::factory()->create(['role' => Peran::WARGA]);
    }

    public function test_warga_dapat_mengirim_laporan_dan_status_awal_dikirim(): void
    {
        $warga = $this->warga();
        $kategori = KategoriMasalah::factory()->create();

        $this->actingAs($warga)->post(route('laporan.simpan'), [
            'kategori_masalah_id' => $kategori->id,
            'judul' => 'Lampu jalan mati',
            'deskripsi' => 'Sudah tiga malam padam.',
            'lokasi' => 'Depan Blok C No. 12',
            'prioritas' => 'tinggi',
        ])->assertRedirect();

        $laporan = Laporan::firstOrFail();
        $this->assertSame(StatusLaporan::DIKIRIM, $laporan->status);
        $this->assertSame($warga->id, $laporan->user_id);
        $this->assertSame('SW-0001', $laporan->nomorTiket());
    }

    public function test_laporan_wajib_menyertakan_kategori_dan_lokasi(): void
    {
        $this->actingAs($this->warga())->post(route('laporan.simpan'), [
            'judul' => 'Tanpa kategori',
            'deskripsi' => 'x',
            'prioritas' => 'sedang',
        ])->assertSessionHasErrors(['kategori_masalah_id', 'lokasi']);
    }

    public function test_pengurus_mengubah_status_ke_diproses_dan_mencatat_waktu_respons(): void
    {
        $pengurus = User::factory()->create(['role' => Peran::PENGURUS]);
        $laporan = Laporan::factory()->create(['status' => StatusLaporan::DIKIRIM]);

        $this->actingAs($pengurus)->put(route('pengurus.laporan.update', $laporan), [
            'status' => StatusLaporan::DIPROSES,
            'prioritas' => 'tinggi',
            'tanggapan' => 'Petugas menuju lokasi.',
        ])->assertRedirect();

        $laporan->refresh();
        $this->assertSame(StatusLaporan::DIPROSES, $laporan->status);
        $this->assertSame('tinggi', $laporan->prioritas);
        $this->assertSame($pengurus->id, $laporan->ditangani_oleh);
        $this->assertNotNull($laporan->ditangani_pada);
        $this->assertDatabaseHas('tanggapans', ['laporan_id' => $laporan->id, 'status_baru' => StatusLaporan::DIPROSES]);
    }

    public function test_warga_lain_tidak_boleh_membuka_laporan_milik_orang_lain(): void
    {
        $laporan = Laporan::factory()->create();

        $this->actingAs($this->warga())->get(route('laporan.show', $laporan))->assertForbidden();
    }

    public function test_warga_tidak_boleh_masuk_area_pengurus_maupun_admin(): void
    {
        $warga = $this->warga();

        $this->actingAs($warga)->get(route('pengurus.laporan.index'))->assertForbidden();
        $this->actingAs($warga)->get(route('admin.pengguna'))->assertForbidden();
    }

    public function test_admin_mengelola_pengguna_dan_tidak_bisa_menghapus_dirinya_sendiri(): void
    {
        $admin = User::factory()->create(['role' => Peran::ADMIN]);

        $this->actingAs($admin)->post(route('admin.pengguna.simpan'), [
            'name' => 'Petugas Baru', 'email' => 'petugas@siagawarga.test',
            'role' => Peran::PENGURUS, 'password' => 'rahasia12345',
        ])->assertRedirect();
        $this->assertDatabaseHas('users', ['email' => 'petugas@siagawarga.test', 'role' => Peran::PENGURUS]);

        $this->actingAs($admin)->delete(route('admin.pengguna.hapus', $admin))->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('masuk'));
    }
}
