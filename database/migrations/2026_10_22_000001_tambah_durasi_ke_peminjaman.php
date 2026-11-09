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
            $t->unsignedTinyInteger('durasi_hari')->default(3);
            $t->date('batas_kembali')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table($this->tabel(), function (Blueprint $t) {
            $t->dropColumn(['durasi_hari', 'batas_kembali']);
        });
    }
};