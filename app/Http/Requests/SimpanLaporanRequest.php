<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SimpanLaporanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'kategori_masalah_id' => ['required', 'exists:kategori_masalahs,id'],
            'judul' => ['required', 'string', 'max:120'],
            'deskripsi' => ['required', 'string', 'max:2000'],
            'lokasi' => ['required', 'string', 'max:180'],
            'prioritas' => ['required', 'in:rendah,sedang,tinggi'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kategori_masalah_id' => 'kategori masalah',
            'lokasi' => 'lokasi acuan',
            'foto' => 'foto bukti',
        ];
    }
}
