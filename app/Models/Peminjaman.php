<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Peminjaman extends Model
{
    protected $table = 'peminjaman';

    protected $fillable = [
        'user_id',
        'barang_id',
        'admin_id',
        'jumlah',
        'kode_struk',
        'status',
        'catatan',
        'tanggal_pengajuan',
        'tanggal_ambil',
        'tanggal_kembali',
        'alasan_tolak',
        'kondisi_kembali',
        'catatan_kembali',
        'perpanjang_hari',
        'perpanjang_alasan',
        'perpanjang_status',
        'jumlah_perpanjang',
    ];

    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
        'tanggal_ambil' => 'datetime',
        'tanggal_kembali' => 'datetime',
    ];

    public function peminjam()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}