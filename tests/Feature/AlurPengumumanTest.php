<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Peran;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlurPengumumanTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengurus_mempublikasikan_pengumuman_dan_warga_membacanya(): void
    {
        $pengurus = User::factory()->create(['role' => Peran::PENGURUS]);

        $this->actingAs($pengurus)->post(route('pengumuman.simpan'), [
            'judul' => 'Kerja Bakti Minggu Pagi',
            'isi' => 'Kumpul pukul 07.00 di pertigaan Gang Melati.',
        ])->assertRedirect();

        $this->assertDatabaseHas('pengumumans', ['judul' => 'Kerja Bakti Minggu Pagi']);

        $this->actingAs(User::factory()->create(['role' => Peran::WARGA]))
            ->get(route('pengumuman.index'))
            ->assertOk()
            ->assertSee('Kerja Bakti Minggu Pagi');
    }

    public function test_warga_tidak_boleh_membuat_pengumuman(): void
    {
        $this->actingAs(User::factory()->create(['role' => Peran::WARGA]))
            ->get(route('pengumuman.buat'))
            ->assertForbidden();
    }
}
