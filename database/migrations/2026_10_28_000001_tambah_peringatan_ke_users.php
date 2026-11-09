<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->text('peringatan')->nullable();                 // isi peringatan dari admin
            $t->timestamp('peringatan_at')->nullable();         // kapan dikirim
            $t->timestamp('peringatan_dibaca_at')->nullable();  // kapan user menekan "Saya mengerti"
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn(['peringatan', 'peringatan_at', 'peringatan_dibaca_at']);
        });
    }
};