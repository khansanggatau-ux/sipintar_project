<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // fitur kode unit dihapus, tabelnya ikut dibuang
        Schema::dropIfExists('peminjaman_unit');
        Schema::dropIfExists('unit_barang');
    }

    public function down(): void
    {
        $peminjaman = (new \App\Models\Peminjaman)->getTable();

        Schema::create('unit_barang', function (Blueprint $t) {
            $t->id();
            $t->foreignId('barang_id')->constrained('barang')->cascadeOnDelete();
            $t->string('kode')->unique();
            $t->string('status')->default('tersedia');
            $t->timestamps();
        });

        Schema::create('peminjaman_unit', function (Blueprint $t) use ($peminjaman) {
            $t->id();
            $t->foreignId('peminjaman_id')->constrained($peminjaman)->cascadeOnDelete();
            $t->foreignId('unit_id')->constrained('unit_barang')->cascadeOnDelete();
            $t->unique(['peminjaman_id', 'unit_id']);
        });
    }
};