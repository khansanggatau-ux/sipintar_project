<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Bantuan kecil untuk urusan batas kembali, keterlambatan, dan tautan WhatsApp.
 * File baru: tidak mengubah file lama apa pun.
 */
class Pinjam
{
    /** Tanggal batas kembali (atau null kalau belum ada). */
    public static function batas($p): ?Carbon
    {
        return empty($p->batas_kembali) ? null : Carbon::parse($p->batas_kembali)->startOfDay();
    }

    /** Berapa hari terlambat (0 kalau belum terlambat / bukan sedang dipinjam). */
    public static function telat($p): int
    {
        $b = self::batas($p);
        if ($p->status !== 'dipinjam' || ! $b) {
            return 0;
        }
        return today()->gt($b) ? $b->diffInDays(today()) : 0;
    }

    /** Sisa hari sampai batas kembali (negatif = sudah lewat, null = tidak berlaku). */
    public static function sisa($p): ?int
    {
        $b = self::batas($p);
        if ($p->status !== 'dipinjam' || ! $b) {
            return null;
        }
        return today()->diffInDays($b, false);
    }

    /** Tautan chat WhatsApp dengan pesan yang sudah terisi. */
    public static function wa(?string $nomor, string $teks): string
    {
        $n = preg_replace('/\D+/', '', (string) $nomor);
        if ($n !== '' && str_starts_with($n, '0')) {
            $n = '62' . substr($n, 1);
        }
        return 'https://wa.me/' . $n . '?text=' . rawurlencode($teks);
    }
}