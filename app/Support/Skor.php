<?php

namespace App\Support;

use App\Models\Peminjaman;
use Carbon\Carbon;

/**
 * Skor kepercayaan peminjam, dihitung dari riwayatnya (tidak disimpan di database).
 * Mulai 100, lalu dikurangi: telat kembali -10, barang rusak -20, barang hilang -40,
 * sedang telat sekarang -15 (per barang).
 */
class Skor
{
    /** Skor satu user (ambil riwayatnya dari database). */
    public static function untuk(int $userId): array
    {
        $semua = Peminjaman::where('user_id', $userId)
            ->whereIn('status', ['dipinjam', 'selesai'])
            ->get();

        return self::hitung($semua);
    }

    /** Hitung skor dari kumpulan peminjaman (status dipinjam/selesai) milik SATU user. */
    public static function hitung($semua): array
    {
        $tepat = 0;
        $telat = 0;
        $rusak = 0;
        $hilang = 0;
        $sedangTelat = 0;

        foreach ($semua as $p) {
            if ($p->status === 'dipinjam') {
                if (Pinjam::telat($p) > 0) {
                    $sedangTelat++;
                }
                continue;
            }

            $terlambat = false;
            if ($p->batas_kembali && $p->tanggal_kembali) {
                $kembali = $p->tanggal_kembali->copy()->startOfDay();
                $batas = Carbon::parse($p->batas_kembali)->startOfDay();
                $terlambat = $kembali->gt($batas);
            }

            if ($terlambat) {
                $telat++;
            } else {
                $tepat++;
            }

            if ($p->kondisi_kembali === 'rusak') {
                $rusak++;
            }
            if ($p->kondisi_kembali === 'hilang') {
                $hilang++;
            }
        }

        $baru = $semua->count() === 0;
        $skor = 100 - ($telat * 10) - ($rusak * 20) - ($hilang * 40) - ($sedangTelat * 15);
        $skor = max(0, min(100, $skor));

        if ($baru) {
            $label = 'Peminjam baru';
            $badge = '';
        } elseif ($skor >= 85) {
            $label = 'Terpercaya';
            $badge = 'badge-green';
        } elseif ($skor >= 60) {
            $label = 'Cukup baik';
            $badge = 'badge-amber';
        } else {
            $label = 'Perlu diawasi';
            $badge = 'badge-red';
        }

        return [
            'baru' => $baru,
            'skor' => $skor,
            'label' => $label,
            'badge' => $badge,
            'selesai' => $tepat + $telat,
            'tepat' => $tepat,
            'telat' => $telat,
            'rusak' => $rusak,
            'hilang' => $hilang,
            'sedangTelat' => $sedangTelat,
        ];
    }

    /** Berapa barang yang sedang dipinjam user ini dan sudah lewat batas kembali. */
    public static function sedangTelat(int $userId): int
    {
        return Peminjaman::where('user_id', $userId)
            ->where('status', 'dipinjam')
            ->get()
            ->filter(function ($p) {
                return Pinjam::telat($p) > 0;
            })
            ->count();
    }
}