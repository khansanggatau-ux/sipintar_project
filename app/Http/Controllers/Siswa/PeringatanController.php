<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Support\Skor;

class PeringatanController extends Controller
{
    // Halaman peringatan dari admin (muncul paling pertama setelah login)
    public function show()
    {
        $user = auth()->user();

        if (empty($user->peringatan) || $user->peringatan_dibaca_at !== null) {
            return redirect()->route('siswa.inventaris.index');
        }

        $skor = Skor::untuk($user->id);

        return view('siswa.peringatan', compact('user', 'skor'));
    }

    // Tombol "Saya mengerti"
    public function baca()
    {
        $user = auth()->user();

        if (! empty($user->peringatan) && $user->peringatan_dibaca_at === null) {
            $user->update(['peringatan_dibaca_at' => now()]);
        }

        return redirect()->route('siswa.inventaris.index');
    }
}