<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Antrian;
use App\Models\Barang;
use App\Models\Peminjaman;
use App\Support\Skor;
use Illuminate\Http\Request;

class InventarisController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->query('q');
        $selectedId = $request->query('selected');

        // dibuka dari notifikasi "sudah tersedia" -> daftar tunggu barang itu dibersihkan
        $buka = $request->query('buka');
        if ($buka) {
            $bk = Barang::find($buka);
            if ($bk && $bk->stok > 0) {
                Antrian::where('user_id', auth()->id())->where('barang_id', $bk->id)->delete();
            }
        }

        $barang = Barang::when($q, fn ($query) => $query->where('nama', 'like', "%{$q}%"))
            ->orderBy('nama')
            ->get();

        $selected = $selectedId ? Barang::find($selectedId) : null;

        // id barang yang sedang ditunggu siswa ini
        $antrianIds = Antrian::where('user_id', auth()->id())
            ->pluck('barang_id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->all();

        // barang yang sedang diajukan / dipinjam siswa ini (per barang hanya boleh 1)
        $aktif = Peminjaman::where('user_id', auth()->id())
            ->whereIn('status', ['menunggu', 'dipinjam'])
            ->pluck('status', 'barang_id')
            ->all();

        return view('siswa.inventaris', compact('barang', 'selected', 'q', 'antrianIds', 'aktif'));
    }

    // FR-04: Ajukan Peminjaman -> validasi stok otomatis
    public function ajukan(Request $request, Barang $barang)
    {
        $data = $request->validate([
            'jumlah' => 'required|integer|min:1|max:1',
            'catatan' => 'nullable|string|max:255',
        ], [
            'jumlah.max' => 'Satu pengajuan hanya boleh 1 unit.',
        ]);

        // masih punya barang yang telat dikembalikan -> tidak boleh mengajukan lagi
        if (Skor::sedangTelat(auth()->id()) > 0) {
            return back()->with('status', 'Kamu masih punya barang yang terlambat dikembalikan. Kembalikan dulu ya sebelum mengajukan lagi.');
        }

        // satu akun hanya boleh punya 1 peminjaman aktif untuk tiap barang
        $sudahAda = Peminjaman::where('user_id', auth()->id())
            ->where('barang_id', $barang->id)
            ->whereIn('status', ['menunggu', 'dipinjam'])
            ->first();

        if ($sudahAda) {
            $pesan = $sudahAda->status === 'dipinjam'
                ? 'Kamu masih meminjam ' . $barang->nama . '. Kembalikan dulu ke admin sebelum meminjam lagi.'
                : 'Pengajuan ' . $barang->nama . ' kamu masih menunggu persetujuan admin. Satu akun hanya boleh 1 peminjaman per barang.';

            return back()->withInput()->withErrors(['jumlah' => $pesan]);
        }

        if ($barang->stok <= 0) {
            return back()->withErrors(['jumlah' => 'Stok barang ini sedang habis.']);
        }

        if ($data['jumlah'] > $barang->stok) {
            return back()->withErrors(['jumlah' => 'Jumlah melebihi stok yang tersedia (' . $barang->stok . ').']);
        }

        $peminjaman = Peminjaman::create([
            'user_id' => auth()->id(),
            'barang_id' => $barang->id,
            'jumlah' => $data['jumlah'],
            'catatan' => $data['catatan'] ?? null,
            'kode_struk' => 'STR-' . random_int(10000, 99999),
            'status' => 'menunggu',
            'tanggal_pengajuan' => now(),
        ]);

        // sudah mengajukan -> tidak perlu lagi ada di daftar tunggu barang ini
        Antrian::where('user_id', auth()->id())->where('barang_id', $barang->id)->delete();

        // FR-05: struk digital langsung muncul setelah submit
        return redirect()->route('siswa.pengajuan.index')->with('struk', $peminjaman->id);
    }

    // "Kabari aku kalau tersedia": masuk / keluar daftar tunggu barang yang habis
    public function kabari(Barang $barang)
    {
        if ($barang->stok > 0) {
            return back()->with('status', 'Barang ini sudah tersedia, langsung ajukan aja.');
        }

        $ada = Antrian::where('user_id', auth()->id())->where('barang_id', $barang->id)->first();

        if ($ada) {
            $ada->delete();

            return back()->with('status', 'Oke, kamu tidak akan dikabari lagi untuk ' . $barang->nama . '.');
        }

        Antrian::create(['user_id' => auth()->id(), 'barang_id' => $barang->id]);

        return back()->with('status', 'Siap! Kamu dikabari lewat lonceng notifikasi begitu ' . $barang->nama . ' tersedia.');
    }
}