<?php

namespace App\Observers;

use App\Models\Peminjaman;
use Illuminate\Support\Facades\Schema;

/**
 * Mengisi lama pinjam saat pengajuan dibuat, dan menghitung batas kembali
 * saat admin menyetujui pengambilan barang. Controller lama tidak perlu diubah.
 */
class PeminjamanObserver
{
    private static ?bool $kolomAda = null;

    private function kolomAda(Peminjaman $p): bool
    {
        return self::$kolomAda ??= Schema::hasColumn($p->getTable(), 'batas_kembali');
    }

    public function creating(Peminjaman $p): void
    {
        if (! $this->kolomAda($p)) {
            return;
        }
        $hari = (int) request()->input('durasi_hari', 3);
        $p->durasi_hari = max(1, min(14, $hari));
    }

    public function updating(Peminjaman $p): void
    {
        if (! $this->kolomAda($p)) {
            return;
        }
        if ($p->isDirty('status') && $p->status === 'dipinjam') {
            $p->batas_kembali = now()->addDays((int) ($p->durasi_hari ?: 3))->toDateString();
        }
    }
}