<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $t) {
            // berapa unit barang ini yang sedang rusak (tidak ikut dihitung di stok tersedia)
            $t->unsignedInteger('jumlah_rusak')->default(0)->after('kondisi');
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $t) {
            $t->dropColumn('jumlah_rusak');
        });
    }
};