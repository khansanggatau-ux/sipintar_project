<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barang', function (Blueprint $t) {
            // baik | rusak_ringan | rusak_berat  (semua barang lama otomatis "baik")
            $t->string('kondisi', 20)->default('baik')->after('stok');
            $t->string('ket_kondisi')->nullable()->after('kondisi');
        });
    }

    public function down(): void
    {
        Schema::table('barang', function (Blueprint $t) {
            $t->dropColumn(['kondisi', 'ket_kondisi']);
        });
    }
};