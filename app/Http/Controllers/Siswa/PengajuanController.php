<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Support\Pinjam;
use Illuminate\Http\Request;

class PengajuanController extends Controller
{
    public function index()
    {
        $list = Peminjaman::with('barang')
            ->where('user_id', auth()->id())
            ->latest('tanggal_pengajuan')
            ->get();

        return view('siswa.pengajuan', compact('list'));
    }

    // Siswa membatalkan pengajuannya sendiri (hanya selama masih "menunggu")
    public function batal(Peminjaman $peminjaman)
    {
        if ($peminjaman->user_id !== auth()->id()) {
            abort(403);
        }

        if ($peminjaman->status !== 'menunggu') {
            return redirect()->route('siswa.pengajuan.index')
                ->with('status', 'Pengajuan ini sudah diproses admin, tidak bisa dibatalkan.');
        }

        $peminjaman->delete();

        return redirect()->route('siswa.pengajuan.index')
            ->with('status', 'Pengajuan dibatalkan.');
    }

    // Minta tambah hari untuk barang yang sedang dipinjam (maksimal 2x)
    public function perpanjang(Request $request, Peminjaman $peminjaman)
    {
        if ($peminjaman->user_id !== auth()->id()) {
            abort(403);
        }

        $kembali = redirect()->route('siswa.pengajuan.index');

        if ($peminjaman->status !== 'dipinjam') {
            return $kembali->with('status', 'Perpanjangan hanya bisa untuk barang yang sedang dipinjam.');
        }

        if (Pinjam::telat($peminjaman) > 0) {
            return $kembali->with('status', 'Barang ini sudah terlambat. Kembalikan ke admin, ya.');
        }

        if ($peminjaman->perpanjang_status === 'menunggu') {
            return $kembali->with('status', 'Permintaan perpanjanganmu masih menunggu admin.');
        }

        if ((int) $peminjaman->jumlah_perpanjang >= 2) {
            return $kembali->with('status', 'Perpanjangan sudah dipakai 2 kali, itu batas maksimal.');
        }

        $hari = (int) $request->input('hari', 3);
        if (! in_array($hari, [1, 2, 3, 5, 7], true)) {
            $hari = 3;
        }

        $alasan = trim((string) $request->input('alasan', ''));

        $peminjaman->perpanjang_hari = $hari;
        $peminjaman->perpanjang_alasan = $alasan === '' ? null : mb_substr($alasan, 0, 255);
        $peminjaman->perpanjang_status = 'menunggu';
        $peminjaman->save();

        return $kembali->with('status', 'Permintaan tambah ' . $hari . ' hari terkirim. Tunggu persetujuan admin ya.');
    }
}