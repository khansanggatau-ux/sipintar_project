<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('username')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'siswa'])->default('siswa');
            $table->enum('tipe', ['Siswa', 'Guru'])->nullable();
            $table->string('jurusan')->nullable();
            $table->string('kelas')->nullable();
            $table->string('no_wa')->nullable();
            $table->boolean('profile_complete')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
