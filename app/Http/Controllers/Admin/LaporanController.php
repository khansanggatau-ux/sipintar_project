<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Laporan;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    // Halaman rapi untuk dicetak / disimpan jadi PDF lewat browser (Ctrl+P)
    public function cetak(Request $request)
    {
        $dari = Laporan::tanggal($request->query('dari'));
        $sampai = Laporan::tanggal($request->query('sampai'));
        $data = Laporan::ambil($dari, $sampai);

        return view('admin.laporan-cetak', compact('data', 'dari', 'sampai'));
    }

    // File CSV, bisa langsung dibuka di Excel
    public function csv(Request $request)
    {
        $dari = Laporan::tanggal($request->query('dari'));
        $sampai = Laporan::tanggal($request->query('sampai'));
        $data = Laporan::ambil($dari, $sampai);

        $namaFile = 'laporan-peminjaman-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // supaya Excel baca UTF-8 dengan benar

            fputcsv($out, [
                'No. Struk', 'Peminjam', 'Kelas', 'Barang', 'Jumlah',
                'Tgl Pengajuan', 'Tgl Ambil', 'Batas Kembali', 'Tgl Kembali',
                'Status', 'Kondisi Kembali', 'Catatan Kembali', 'Alasan Ditolak',
            ], ';');

            foreach ($data as $p) {
                fputcsv($out, array_map([self::class, 'aman'], [
                    $p->kode_struk,
                    optional($p->peminjam)->name,
                    optional($p->peminjam)->kelas,
                    optional($p->barang)->nama,
                    $p->jumlah,
                    optional($p->tanggal_pengajuan)->format('d/m/Y H:i'),
                    optional($p->tanggal_ambil)->format('d/m/Y H:i'),
                    $p->batas_kembali ? \Carbon\Carbon::parse($p->batas_kembali)->format('d/m/Y') : '',
                    optional($p->tanggal_kembali)->format('d/m/Y H:i'),
                    Laporan::labelStatus($p->status),
                    $p->kondisi_kembali,
                    $p->catatan_kembali,
                    $p->alasan_tolak,
                ]), ';');
            }

            fclose($out);
        }, $namaFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // Cegah teks yang diawali = + - @ dibaca Excel sebagai rumus
    public static function aman($nilai)
    {
        if (is_string($nilai) && $nilai !== '' && strpbrk($nilai[0], '=+-@') !== false) {
            return "'" . $nilai;
        }

        return $nilai;
    }
}