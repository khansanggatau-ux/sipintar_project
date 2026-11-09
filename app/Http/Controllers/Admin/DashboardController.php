<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Peminjaman;
use App\Support\Laporan;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $dari = Laporan::tanggal($request->query('dari'));
        $sampai = Laporan::tanggal($request->query('sampai'));
        $laporan = Laporan::ambil($dari, $sampai);

        $totalUnit = Barang::sum('stok');
        $tersedia = Barang::where('stok', '>', 2)->count();
        $sedikit = Barang::whereBetween('stok', [1, 2])->count();
        $habis = Barang::where('stok', 0)->count();
        $menunggu = Peminjaman::where('status', 'menunggu')->count();
        $dipinjam = Peminjaman::where('status', 'dipinjam')->count();
        $recent = Peminjaman::with(['peminjam', 'barang'])->latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalUnit', 'tersedia', 'sedikit', 'habis', 'menunggu', 'dipinjam', 'recent',
            'laporan', 'dari', 'sampai'
        ));
    }
}