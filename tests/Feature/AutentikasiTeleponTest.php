<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Peran;
use App\Support\Telepon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AutentikasiTeleponTest extends TestCase
{
    use RefreshDatabase;

    public function test_nomor_hp_dinormalkan_dari_berbagai_penulisan(): void
    {
        $this->assertSame('6281234567890', Telepon::normalisasi('0812-3456-7890'));
        $this->assertSame('6281234567890', Telepon::normalisasi('+62 812 3456 7890'));
        $this->assertSame('6281234567890', Telepon::normalisasi('6281234567890'));
        $this->assertSame('6281234567890', Telepon::normalisasi('812 3456 7890'));
        $this->assertSame('0812-3456-7890', Telepon::tampilkan('6281234567890'));
    }

    public function test_warga_masuk_pakai_nomor_hp_walau_ditulis_berbeda(): void
    {
        $warga = User::factory()->create([
            'telepon' => '6281234567890', 'password' => 'rahasia12345', 'role' => Peran::WARGA,
        ]);

        $this->post(route('masuk'), ['telepon' => '0812-3456-7890', 'password' => 'rahasia12345'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($warga);
    }

    public function test_daftar_menolak_nomor_hp_yang_sudah_terpakai(): void
    {
        User::factory()->create(['telepon' => '6281200000009']);

        $this->post(route('daftar'), [
            'name' => 'Warga Baru', 'telepon' => '0812-0000-0009', 'rt_rw' => 'RT 01 / RW 01',
            'password' => 'rahasia12345', 'password_confirmation' => 'rahasia12345',
        ])->assertSessionHasErrors('telepon');

        $this->assertGuest();
    }

    public function test_warga_boleh_daftar_tanpa_email(): void
    {
        $this->post(route('daftar'), [
            'name' => 'Ibu Sari', 'telepon' => '0812-7777-6666', 'rt_rw' => 'RT 03 / RW 02',
            'password' => 'rahasia12345', 'password_confirmation' => 'rahasia12345',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', ['telepon' => '6281277776666', 'role' => Peran::WARGA, 'email' => null]);
    }

    public function test_percobaan_masuk_dibatasi_lima_kali(): void
    {
        RateLimiter::clear('masuk:6281111111111|127.0.0.1');
        User::factory()->create(['telepon' => '6281111111111']);

        foreach (range(1, 5) as $_) {
            $this->post(route('masuk'), ['telepon' => '0811-1111-1111', 'password' => 'salah'])
                ->assertSessionHasErrors('telepon');
        }

        $this->post(route('masuk'), ['telepon' => '0811-1111-1111', 'password' => 'salah'])
            ->assertSessionHasErrors('telepon');

        $this->assertStringContainsString('Terlalu banyak', session('errors')->first('telepon'));
    }
}
