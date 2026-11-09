<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function tabelPeminjaman(): string
    {
        return (new \App\Models\Peminjaman)->getTable();
    }

    public function up(): void
    {
        $peminjaman = $this->tabelPeminjaman();

        // 1. Daftar tunggu barang yang habis
        Schema::create('antrian_barang', function (Blueprint $t) use ($peminjaman) {
            $t->id();
            $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $t->foreignId('barang_id')->constrained('barang')->cascadeOnDelete();
            $t->timestamps();
            $t->unique(['user_id', 'barang_id']);
        });

        // 2. Kode unit per barang
        Schema::create('unit_barang', function (Blueprint $t) {
            $t->id();
            $t->foreignId('barang_id')->constrained('barang')->cascadeOnDelete();
            $t->string('kode')->unique();
            $t->string('status')->default('tersedia'); // tersedia / dipinjam / rusak / hilang
            $t->timestamps();
            $t->index(['barang_id', 'status']);
        });

        Schema::create('peminjaman_unit', function (Blueprint $t) use ($peminjaman) {
            $t->id();
            $t->foreignId('peminjaman_id')->constrained($peminjaman)->cascadeOnDelete();
            $t->foreignId('unit_id')->constrained('unit_barang')->cascadeOnDelete();
            $t->unique(['peminjaman_id', 'unit_id']);
        });

        // 5. Perpanjang pinjaman
        Schema::table($peminjaman, function (Blueprint $t) {
            $t->unsignedTinyInteger('perpanjang_hari')->nullable();
            $t->string('perpanjang_alasan')->nullable();
            $t->string('perpanjang_status')->nullable(); // menunggu / disetujui / ditolak
            $t->unsignedTinyInteger('jumlah_perpanjang')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table($this->tabelPeminjaman(), function (Blueprint $t) {
            $t->dropColumn(['perpanjang_hari', 'perpanjang_alasan', 'perpanjang_status', 'jumlah_perpanjang']);
        });

        Schema::dropIfExists('peminjaman_unit');
        Schema::dropIfExists('unit_barang');
        Schema::dropIfExists('antrian_barang');
    }
};