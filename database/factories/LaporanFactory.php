<?php

namespace Database\Factories;

use App\Models\KategoriMasalah;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LaporanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'kategori_masalah_id' => KategoriMasalah::factory(),
            'judul' => fake()->sentence(4),
            'deskripsi' => fake()->paragraph(),
            'lokasi' => 'Blok '.fake()->randomLetter().' No. '.fake()->numberBetween(1, 30),
            'prioritas' => fake()->randomElement(['rendah', 'sedang', 'tinggi']),
            'status' => 'dikirim',
        ];
    }
}
