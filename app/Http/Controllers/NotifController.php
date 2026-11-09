<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use App\Models\Peminjaman;
use App\Support\Pinjam;
use Illuminate\Support\Facades\Schema;

class NotifController extends Controller
{
    /** Ringkasan untuk lonceng admin. */
    public function admin()
    {
        $tabel = (new Peminjaman)->getTable();
        $menunggu = Peminjaman::where('status', 'menunggu')->count();

        $terlambat = 0;
        if (Schema::hasColumn($tabel, 'batas_kembali')) {
            $terlambat = Peminjaman::where('status', 'dipinjam')
                ->whereDate('batas_kembali', '<', today())
                ->count();
        }

        // permintaan perpanjang yang menunggu persetujuan
        $perpanjang = 0;
        if (Schema::hasColumn($tabel, 'perpanjang_status')) {
            $perpanjang = Peminjaman::where('status', 'dipinjam')
                ->where('perpanjang_status', 'menunggu')
                ->count();
        }

        $baru = Peminjaman::with(['barang', 'peminjam'])
            ->where('status', 'menunggu')
            ->latest('id')->take(5)->get()
            ->map(function ($p) {
                return [
                    'nama'   => optional($p->peminjam)->name ?: '-',
                    'barang' => optional($p->barang)->nama ?: '-',
                    'waktu'  => $p->created_at ? $p->created_at->locale('id')->diffForHumans() : '',
                    'href'   => route('admin.peminjaman.index', ['tab' => 'menunggu', 'selected' => $p->id]),
                ];
            })->values();

        return response()->json([
            'menunggu'   => $menunggu,
            'terlambat'  => $terlambat,
            'perpanjang' => $perpanjang,
            'baru'       => $baru,
            'links'      => [
                'menunggu' => route('admin.peminjaman.index', ['tab' => 'menunggu']),
                'dipinjam' => route('admin.peminjaman.index', ['tab' => 'dipinjam']),
            ],
        ]);
    }

    /** Status pengajuan milik siswa yang sedang login. */
    public function siswa()
    {
        $items = Peminjaman::with('barang')
            ->whereHas('peminjam', function ($q) {
                $q->whereKey(auth()->id());
            })
            ->latest('id')->take(30)->get()
            ->map(function ($p) {
                return [
                    'id'         => $p->id,
                    'status'     => $p->status,
                    'barang'     => optional($p->barang)->nama ?: '-',
                    'telat'      => Pinjam::telat($p),
                    'sisa'       => Pinjam::sisa($p),
                    'perpanjang' => $p->perpanjang_status,
                ];
            })->values();

        // barang yang ditunggu siswa ini dan sekarang sudah tersedia
        $tersedia = [];
        if (Schema::hasTable('antrian_barang')) {
            $tersedia = Antrian::with('barang')
                ->where('user_id', auth()->id())
                ->get()
                ->filter(function ($a) {
                    return $a->barang && $a->barang->stok > 0;
                })
                ->map(function ($a) {
                    return [
                        'id'     => $a->barang->id,
                        'barang' => $a->barang->nama,
                        'href'   => route('siswa.inventaris.index', ['buka' => $a->barang->id]),
                    ];
                })->values();
        }

        // pesan dari admin (lewat tombol Notif web / peringatan) yang belum dibaca
        $u = auth()->user();
        $pesanAdmin = null;
        if (! empty($u->peringatan) && $u->peringatan_dibaca_at === null) {
            $pesanAdmin = [
                'at'       => optional($u->peringatan_at)->timestamp ?: 1,
                'cuplikan' => mb_strimwidth((string) $u->peringatan, 0, 80, '...'),
                'href'     => route('siswa.peringatan'),
            ];
        }

        return response()->json([
            'pesan'    => $pesanAdmin,
            'items'    => $items,
            'tersedia' => $tersedia,
            'link'     => route('siswa.pengajuan.index'),
        ]);
    }
}