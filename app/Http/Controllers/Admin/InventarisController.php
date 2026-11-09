<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class InventarisController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');

        // batas_kembali bisa belum ada di database lama, jadi dicek dulu
        $punyaBatas = Schema::hasColumn((new Peminjaman)->getTable(), 'batas_kembali');

        $barang = Barang::with(['antrian' => fn ($query) => $query->oldest()->with('user')])
            ->withCount([
                'antrian',
                // pengajuan yang menunggu persetujuan admin
                'peminjaman as menunggu_count' => fn ($query) => $query->where('status', 'menunggu'),
                // peminjaman yang sudah lewat batas kembali
                'peminjaman as terlambat_count' => function ($query) use ($punyaBatas) {
                    $punyaBatas
                        ? $query->where('status', 'dipinjam')->whereDate('batas_kembali', '<', today())
                        : $query->whereRaw('1 = 0');
                },
            ])
            // jumlah unit yang sedang dipinjam
            ->withSum(['peminjaman as dipinjam_unit' => fn ($query) => $query->where('status', 'dipinjam')], 'jumlah')
            ->when($q, fn ($query) => $query->where('nama', 'like', "%{$q}%"))
            ->orderBy('nama')
            ->get();

        return view('admin.inventaris', compact('barang', 'q'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'lokasi' => 'nullable|string|max:100',
            'stok' => 'nullable|integer|min:0',
            'kondisi' => 'nullable|in:baik,rusak_ringan,rusak_berat',
            'ket_kondisi' => 'nullable|string|max:255',
        ]);

        $data['stok'] = $data['stok'] ?? 0;
        $data['kondisi'] = $data['kondisi'] ?? 'baik';
        $data['icon'] = '&#128230;';

        Barang::create($data);

        return back()->with('status', 'Barang berhasil ditambahkan.');
    }

    public function update(Request $request, Barang $barang)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:100',
            'lokasi' => 'nullable|string|max:100',
            'stok' => 'required|integer|min:0',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat',
            'ket_kondisi' => 'nullable|string|max:255',
            'jumlah_rusak' => 'nullable|integer|min:0',
        ]);

        $data['jumlah_rusak'] = $data['jumlah_rusak'] ?? 0;

        $barang->update($data);

        return back()->with('status', 'Perubahan barang disimpan.' . $this->infoAntrian($barang));
    }

    public function destroy(Barang $barang)
    {
        $barang->delete();

        return back()->with('status', 'Barang dihapus.');
    }

    public function tambahStok(Request $request, Barang $barang)
    {
        $data = $request->validate([
            'jumlah' => 'required|integer',
        ]);

        $barang->increment('stok', $data['jumlah']);

        return back()->with('status', 'Stok berhasil ditambahkan.' . $this->infoAntrian($barang->fresh()));
    }

    // kalau stok ada dan ada siswa yang antri, mereka otomatis dikabari lewat lonceng
    private function infoAntrian(Barang $barang): string
    {
        $antri = $barang->antrian()->count();

        return ($barang->stok > 0 && $antri > 0)
            ? ' ' . $antri . ' siswa yang antri otomatis dikabari lewat lonceng.'
            : '';
    }
}