<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // warga | pengurus | admin
            $table->string('role')->default('warga')->after('password');
            $table->string('rt_rw')->nullable()->after('role');
            $table->string('telepon')->nullable()->after('rt_rw');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'rt_rw', 'telepon']);
        });
    }
};
