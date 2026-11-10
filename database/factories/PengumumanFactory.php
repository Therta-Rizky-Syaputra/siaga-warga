<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PengumumanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'judul' => fake()->sentence(5),
            'isi' => fake()->paragraph(),
        ];
    }
}
