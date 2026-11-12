<?php

namespace Tests\Feature;

use App\Models\KategoriMasalah;
use App\Models\Laporan;
use App\Models\User;
use App\Support\Kanal;
use App\Support\Peran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerubahanNyataTest extends TestCase
{
    use RefreshDatabase;

    public function test_denyut_menaikkan_versi_setelah_laporan_dikirim(): void
    {
        $warga = User::factory()->create(['role' => Peran::WARGA]);
        $kategori = KategoriMasalah::factory()->create();
        $kanal = app(Kanal::class);

        $sebelum = $kanal->versi();

        $this->actingAs($warga)->post(route('laporan.simpan'), [
            'kategori_masalah_id' => $kategori->id,
            'judul' => 'Lampu mati', 'deskripsi' => 'Gelap', 'lokasi' => 'Blok C', 'prioritas' => 'tinggi',
        ])->assertRedirect();

        $this->assertGreaterThan($sebelum, $kanal->versi());

        // warga menerima angka miliknya sendiri, bukan angka seluruh RT
        $this->actingAs($warga)->getJson(route('perubahan'))
            ->assertOk()
            ->assertJson([
                'versi' => $kanal->versi(),
                'saya_total' => 1,
                'saya_diproses' => 1,
                'saya_selesai' => 0,
            ]);
    }

    public function test_pengurus_melihat_ringkasan_terbaru_lewat_denyut(): void
    {
        $pengurus = User::factory()->create(['role' => Peran::PENGURUS]);
        Laporan::factory()->count(3)->create();

        $this->actingAs($pengurus)->getJson(route('perubahan'))
            ->assertOk()
            ->assertJsonPath('total', 3)
            ->assertJsonPath('dikirim', 3);
    }

    public function test_aliran_hanya_untuk_pengurus(): void
    {
        $warga = User::factory()->create(['role' => Peran::WARGA]);
        $pengurus = User::factory()->create(['role' => Peran::PENGURUS]);

        $this->actingAs($warga)->get(route('perubahan.aliran'))->assertForbidden();
        $this->actingAs($pengurus)->get(route('perubahan.aliran'))->assertOk()
            ->assertHeader('content-type', 'text/event-stream; charset=UTF-8');
    }

    public function test_aliran_melepas_kunci_sesi_agar_halaman_lain_tetap_jalan(): void
    {
        // Aliran panjang tidak boleh memegang lock sesi: kalau dipegang, pengurus
        // yang menonton dashboard tidak bisa membuka laporan lain sama sekali.
        $sumber = file_get_contents(app_path('Http/Controllers/PerubahanController.php'));

        $this->assertStringContainsString('session()->save()', $sumber);
        $this->assertLessThan(
            strpos($sumber, 'eventStream'),
            strpos($sumber, 'session()->save()'),
            'session()->save() harus dipanggil SEBELUM eventStream'
        );
    }

    public function test_dashboard_memaparkan_kunci_yang_dipakai_javascript(): void
    {
        // kontrak DOM: atribut data-hitung berisi NAMA kunci, bukan angkanya;
        // kalau tertukar, halaman tampak benar tapi tidak pernah ikut berubah
        $pengurus = User::factory()->create(['role' => Peran::PENGURUS]);
        Laporan::factory()->count(2)->create();

        $this->actingAs($pengurus)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-hitung="total"', false)
            ->assertSee('data-hitung="prioritas_tinggi"', false)
            ->assertSee('data-hitung="dikirim"', false)
            ->assertSee('data-hitung="pengumuman"', false)
            ->assertSee('data-aliran', false)
            ->assertSee('id="diperbarui-pada"', false);

        $this->actingAs(User::factory()->create(['role' => Peran::WARGA]))->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-hitung="saya_total"', false)
            ->assertSee('data-hitung="saya_diproses"', false)
            ->assertSee('data-hitung="saya_selesai"', false)
            ->assertDontSee('data-aliran', false);   // warga pakai denyut, bukan SSE
    }

    public function test_javascript_mendengarkan_nama_event_yang_dikirim_laravel(): void
    {
        // Laravel mengirim "event: update"; mendengarkan onmessage saja membuat
        // dashboard tampak jalan padahal tidak pernah ikut berubah.
        $js = file_get_contents(resource_path('js/app.js'));

        $this->assertStringContainsString("addEventListener('update'", $js);
        $this->assertStringContainsString('EventSource', $js);
    }

    public function test_berkas_pwa_ada_di_folder_publik(): void
    {
        // berkas statis disajikan web server (nginx/artisan serve), bukan router Laravel,
        // jadi yang diuji di sini keberadaan berkasnya di public/
        foreach (['manifest.webmanifest', 'sw.js', 'offline.html', 'ikon/ikon-192.png'] as $berkas) {
            $this->assertFileExists(public_path($berkas));
        }
    }
}
