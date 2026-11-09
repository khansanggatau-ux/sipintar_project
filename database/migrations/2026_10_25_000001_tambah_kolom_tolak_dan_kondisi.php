<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function tabel(): string
    {
        return (new \App\Models\Peminjaman)->getTable();
    }

    public function up(): void
    {
        Schema::table($this->tabel(), function (Blueprint $t) {
            $t->string('alasan_tolak')->nullable();      // alasan admin menolak pengajuan
            $t->string('kondisi_kembali')->nullable();   // baik / rusak / hilang
            $t->string('catatan_kembali')->nullable();   // catatan admin saat barang kembali
        });
    }

    public function down(): void
    {
        Schema::table($this->tabel(), function (Blueprint $t) {
            $t->dropColumn(['alasan_tolak', 'kondisi_kembali', 'catatan_kembali']);
        });
    }
};