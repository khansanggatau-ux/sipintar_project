<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\Peminjaman;
use App\Support\Skor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'menunggu');
        $selectedId = $request->query('selected');

        $query = Peminjaman::with(['peminjam', 'barang']);

        if ($tab === 'riwayat') {
            // riwayat = yang sudah selesai atau ditolak (100 terbaru)
            $query->whereIn('status', ['selesai', 'ditolak']);
        } else {
            $query->where('status', $tab);
        }

        // 15 data per halaman; ?page=2 dst untuk data yang lebih lama
        $list = $query->orderByDesc('tanggal_pengajuan')->orderByDesc('id')
            ->paginate(15)
            ->appends(['tab' => $tab]);

        $selected = $selectedId
            ? Peminjaman::with(['peminjam', 'barang'])->find($selectedId)
            : null;

        // skor kepercayaan peminjam yang sedang dibuka
        $skor = ($selected && $selected->user_id) ? Skor::untuk((int) $selected->user_id) : null;

        $countMenunggu = Peminjaman::where('status', 'menunggu')->count();
        $countDipinjam = Peminjaman::where('status', 'dipinjam')->count();

        return view('admin.peminjaman', compact('list', 'selected', 'skor', 'tab', 'countMenunggu', 'countDipinjam'));
    }

    // FR-07: Approval Pengambilan Barang -> stok otomatis berkurang
    public function approveAmbil(Peminjaman $peminjaman)
    {
        $gagal = DB::transaction(function () use ($peminjaman) {
            // kunci baris supaya 2 klik / 2 admin tidak bisa memproses bersamaan
            $p = Peminjaman::lockForUpdate()->find($peminjaman->id);

            if (! $p || $p->status !== 'menunggu') {
                return 'Pengajuan ini sudah diproses sebelumnya.';
            }

            $barang = Barang::lockForUpdate()->find($p->barang_id);

            if (! $barang || $barang->stok < $p->jumlah) {
                return 'Stok tidak cukup untuk pengajuan ini (tersisa ' . ($barang ? $barang->stok : 0) . ' unit).';
            }

            $p->update([
                'status' => 'dipinjam',
                'admin_id' => auth()->id(),
                'tanggal_ambil' => now(),
            ]);

            $barang->decrement('stok', $p->jumlah);

            return null;
        });

        return redirect()->route('admin.peminjaman.index', ['tab' => 'menunggu'])
            ->with('status', $gagal ?: 'Pengambilan barang disetujui.');
    }

    // FR-08: Approval Pengembalian Barang
    //  - baik   : semua unit masuk lagi ke stok
    //  - rusak  : hanya unit yang rusak yang tidak masuk stok (sisanya masuk stok)
    //  - hilang : stok tidak ditambah
    // Kalau semua unit barang itu rusak, kondisi barang di inventaris otomatis jadi "Rusak Berat".
    public function approveKembali(Request $request, Peminjaman $peminjaman)
    {
        $kondisi = $request->input('kondisi_kembali', 'baik');
        if (! in_array($kondisi, ['baik', 'rusak', 'hilang'], true)) {
            $kondisi = 'baik';
        }
        $catatan = trim((string) $request->input('catatan_kembali', ''));
        $jumlahRusakInput = (int) $request->input('jumlah_rusak', 1);

        $hasil = ['gagal' => null, 'rusak' => 0, 'balik' => 0];

        DB::transaction(function () use ($peminjaman, $kondisi, $catatan, $jumlahRusakInput, &$hasil) {
            $p = Peminjaman::lockForUpdate()->find($peminjaman->id);

            if (! $p || $p->status !== 'dipinjam') {
                $hasil['gagal'] = 'Pengembalian ini sudah diproses sebelumnya.';
                return;
            }

            $rusak = 0;
            $balik = 0;
            if ($kondisi === 'baik') {
                $balik = (int) $p->jumlah;
            } elseif ($kondisi === 'rusak') {
                $rusak = max(1, min((int) $p->jumlah, $jumlahRusakInput));
                $balik = (int) $p->jumlah - $rusak;
            }

            $catatanAkhir = $catatan;
            if ($rusak > 0) {
                $catatanAkhir = $rusak . ' unit rusak' . ($catatan !== '' ? '. ' . $catatan : '');
            }
            $catatanAkhir = $catatanAkhir === '' ? null : mb_substr($catatanAkhir, 0, 255);

            $p->update([
                'status' => 'selesai',
                'tanggal_kembali' => now(),
                'kondisi_kembali' => $kondisi,
                'catatan_kembali' => $catatanAkhir,
            ]);

            $barang = Barang::lockForUpdate()->find($p->barang_id);
            if ($barang) {
                if ($balik > 0) {
                    $barang->stok = (int) $barang->stok + $balik;
                }

                if ($rusak > 0) {
                    $barang->jumlah_rusak = (int) $barang->jumlah_rusak + $rusak;

                    // unit yang masih bagus = stok tersedia + yang sedang dipinjam orang lain
                    $masihBagus = (int) $barang->stok
                        + (int) Peminjaman::where('barang_id', $barang->id)->where('status', 'dipinjam')->sum('jumlah');

                    if ($masihBagus === 0) {
                        $barang->kondisi = 'rusak_berat';
                        $barang->ket_kondisi = 'Semua unit rusak (' . $barang->jumlah_rusak . ' unit)';
                    } else {
                        $barang->ket_kondisi = $barang->jumlah_rusak . ' unit rusak';
                    }
                }

                $barang->save();
            }

            $hasil['rusak'] = $rusak;
            $hasil['balik'] = $balik;
        });

        if ($hasil['gagal']) {
            $pesan = $hasil['gagal'];
        } elseif ($kondisi === 'baik') {
            $pesan = 'Pengembalian barang disetujui.';
        } elseif ($kondisi === 'rusak') {
            $pesan = 'Pengembalian dicatat: ' . $hasil['rusak'] . ' unit rusak, ' . $hasil['balik'] . ' unit kembali ke stok.';
        } else {
            $pesan = 'Pengembalian dicatat (barang hilang). Stok tidak ditambah.';
        }

        return redirect()->route('admin.peminjaman.index', ['tab' => 'dipinjam'])
            ->with('status', $pesan);
    }

    // Tolak pengajuan (wajib pakai alasan)
    public function tolak(Request $request, Peminjaman $peminjaman)
    {
        $alasan = trim((string) $request->input('alasan', ''));

        if ($alasan === '') {
            return redirect()
                ->route('admin.peminjaman.index', ['tab' => 'menunggu', 'selected' => $peminjaman->id])
                ->with('status', 'Alasan penolakan wajib diisi.');
        }

        $gagal = DB::transaction(function () use ($peminjaman, $alasan) {
            $p = Peminjaman::lockForUpdate()->find($peminjaman->id);

            if (! $p || $p->status !== 'menunggu') {
                return 'Pengajuan ini sudah diproses sebelumnya.';
            }

            $p->update([
                'status' => 'ditolak',
                'admin_id' => auth()->id(),
                'alasan_tolak' => mb_substr($alasan, 0, 255),
            ]);

            return null;
        });

        return redirect()->route('admin.peminjaman.index', ['tab' => 'menunggu'])
            ->with('status', $gagal ?: 'Pengajuan ditolak.');
    }

    // Setujui permintaan perpanjang -> batas kembali ditambah
    public function perpanjangSetuju(Peminjaman $peminjaman)
    {
        $gagal = DB::transaction(function () use ($peminjaman) {
            $p = Peminjaman::lockForUpdate()->find($peminjaman->id);

            if (! $p || $p->status !== 'dipinjam' || $p->perpanjang_status !== 'menunggu') {
                return 'Permintaan perpanjangan ini sudah diproses.';
            }

            $hari = max(1, (int) $p->perpanjang_hari);

            // hitung dari batas kembali lama; kalau batasnya sudah lewat, hitung dari hari ini
            $batas = $p->batas_kembali ? \Carbon\Carbon::parse($p->batas_kembali)->startOfDay() : today();
            if ($batas->lt(today())) {
                $batas = today();
            }

            $p->batas_kembali = $batas->copy()->addDays($hari)->toDateString();
            $p->durasi_hari = min(255, (int) $p->durasi_hari + $hari);
            $p->jumlah_perpanjang = (int) $p->jumlah_perpanjang + 1;
            $p->perpanjang_status = 'disetujui';
            $p->save();

            return null;
        });

        return redirect()
            ->route('admin.peminjaman.index', ['tab' => 'dipinjam', 'selected' => $peminjaman->id])
            ->with('status', $gagal ?: 'Perpanjangan disetujui. Batas kembali sudah diperbarui.');
    }

    // Tolak permintaan perpanjang
    public function perpanjangTolak(Peminjaman $peminjaman)
    {
        $gagal = DB::transaction(function () use ($peminjaman) {
            $p = Peminjaman::lockForUpdate()->find($peminjaman->id);

            if (! $p || $p->status !== 'dipinjam' || $p->perpanjang_status !== 'menunggu') {
                return 'Permintaan perpanjangan ini sudah diproses.';
            }

            $p->perpanjang_status = 'ditolak';
            $p->save();

            return null;
        });

        return redirect()
            ->route('admin.peminjaman.index', ['tab' => 'dipinjam', 'selected' => $peminjaman->id])
            ->with('status', $gagal ?: 'Permintaan perpanjangan ditolak.');
    }
}