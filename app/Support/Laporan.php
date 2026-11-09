<?php

namespace App\Support;

use App\Models\Peminjaman;
use Carbon\Carbon;


class Laporan
{
    public static function tanggal($teks): ?Carbon
    {
        if (! $teks) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', (string) $teks)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function ambil(?Carbon $dari, ?Carbon $sampai)
    {
        return Peminjaman::with(['peminjam', 'barang'])
            ->when($dari, function ($q) use ($dari) {
                $q->where('tanggal_pengajuan', '>=', $dari->copy()->startOfDay());
            })
            ->when($sampai, function ($q) use ($sampai) {
                $q->where('tanggal_pengajuan', '<=', $sampai->copy()->endOfDay());
            })
            ->orderBy('tanggal_pengajuan')
            ->get();
    }

    public static function labelStatus(string $status): string
    {
        return [
            'menunggu' => 'Menunggu',
            'dipinjam' => 'Dipinjam',
            'selesai'  => 'Selesai',
            'ditolak'  => 'Ditolak',
        ][$status] ?? $status;
    }
}