<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // fitur blokir akun sudah dihapus, kolomnya ikut dibuang (kalau ada)
        if (Schema::hasColumn('users', 'aktif')) {
            Schema::table('users', function (Blueprint $t) {
                $t->dropColumn('aktif');
            });
        }

        if (Schema::hasColumn('users', 'alasan_blokir')) {
            Schema::table('users', function (Blueprint $t) {
                $t->dropColumn('alasan_blokir');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->boolean('aktif')->default(true);
            $t->string('alasan_blokir')->nullable();
        });
    }
};