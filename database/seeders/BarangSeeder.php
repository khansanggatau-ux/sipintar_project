<?php

namespace Database\Seeders;

use App\Models\Barang;
use Illuminate\Database\Seeder;

class BarangSeeder extends Seeder
{
    public function run(): void
    {
        // GANTI sesuai tempat penyimpanan barang yang sebenarnya
        $lokasi = 'GUDANG RPL';

        // nama, kategori, stok, ikon
        $data = [
            ['Laptop HP',            'Perangkat Komputer', 3,  '&#128187;'],
            ['Flashdisk',            'Perangkat Komputer', 10, '&#128190;'],
            ['Mouse',                'Perangkat Komputer', 20, '&#128433;'],
            ['Keyboard',             'Perangkat Komputer', 10, '&#9000;'],
            ['Monitor',              'Perangkat Komputer', 3,  '&#128421;'],
            ['Proyektor',            'Elektronik',         3,  '&#128253;'],
            ['Arduino Uno',          'Elektronik',         30, '&#128268;'],
            ['Speaker',              'Elektronik',         1,  '&#128266;'],
            ['Mic + Sound',          'Elektronik',         1,  '&#127908;'],
            ['Obeng Mini (Set)',     'Alat Praktikum',     1,  '&#129691;'],
            ['Solder',               'Alat Praktikum',     1,  '&#9889;'],
            ['Bor Tembok',           'Alat Praktikum',     1,  '&#128296;'],
            ['Bor Mini Listrik',     'Alat Praktikum',     1,  '&#128295;'],
        ];

        Barang::unguard();
        foreach ($data as [$nama, $kategori, $stok, $icon]) {
            Barang::updateOrCreate(
                ['nama' => $nama],
                ['kategori' => $kategori, 'lokasi' => $lokasi, 'stok' => $stok, 'icon' => $icon]
            );
        }
        Barang::reguard();
    }
}