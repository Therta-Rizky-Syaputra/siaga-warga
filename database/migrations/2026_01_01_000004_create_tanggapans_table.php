<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tanggapans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_id')->constrained('laporans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('isi');
            $table->string('status_baru')->nullable();   // status laporan setelah tanggapan ini
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tanggapans');
    }
};
