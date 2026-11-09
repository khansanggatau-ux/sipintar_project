<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';

    protected $fillable = [
        'nama',
        'kategori',
        'lokasi',
        'stok',
        'kondisi',
        'jumlah_rusak',
        'ket_kondisi',
        'icon',
    ];

    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class);
    }

    public function antrian()
    {
        return $this->hasMany(Antrian::class);
    }
}