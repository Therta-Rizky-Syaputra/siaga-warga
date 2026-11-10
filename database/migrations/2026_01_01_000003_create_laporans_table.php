<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kategori_masalah_id')->constrained('kategori_masalahs');
            $table->string('judul');
            $table->text('deskripsi');
            $table->string('lokasi');                 // lokasi acuan teks, mis. "Depan Blok C No. 12"
            $table->string('foto')->nullable();
            $table->enum('prioritas', ['rendah', 'sedang', 'tinggi'])->default('sedang');
            $table->enum('status', ['dikirim', 'diverifikasi', 'diproses', 'selesai', 'ditolak'])->default('dikirim');
            $table->foreignId('ditangani_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ditangani_pada')->nullable();
            $table->timestamps();

            $table->index(['status', 'prioritas']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporans');
    }
};
