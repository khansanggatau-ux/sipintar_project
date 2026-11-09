<?php

namespace Database\Seeders;

use App\Models\Barang;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin SIPINTAR',
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'profile_complete' => true,
        ]);

        $barangList = [
            ['nama' => 'PC Rakitan Core i5', 'lokasi' => 'LAB ASM 1', 'kategori' => 'Perangkat Komputer', 'stok' => 3, 'icon' => '&#128421;'],
            ['nama' => 'Kamera DSLR Canon EOS 1500D', 'lokasi' => 'LAB ASM 2', 'kategori' => 'Elektronik', 'stok' => 0, 'icon' => '&#128247;'],
            ['nama' => 'Router Mikrotik RB941', 'lokasi' => 'LAB RPL 2', 'kategori' => 'Jaringan', 'stok' => 5, 'icon' => '&#128225;'],
            ['nama' => 'Laptop Axioo MyBook', 'lokasi' => 'AXIOO 1', 'kategori' => 'Perangkat Komputer', 'stok' => 4, 'icon' => '&#128187;'],
            ['nama' => 'Tripod Kamera', 'lokasi' => 'LAB ASM 3', 'kategori' => 'Elektronik', 'stok' => 1, 'icon' => '&#127909;'],
            ['nama' => 'Proyektor Epson', 'lokasi' => 'Ruang Teori ASM', 'kategori' => 'Elektronik', 'stok' => 2, 'icon' => '&#128253;'],
            ['nama' => 'Switch TP-Link 8 Port', 'lokasi' => 'LAB RPL 1', 'kategori' => 'Jaringan', 'stok' => 6, 'icon' => '&#128268;'],
            ['nama' => 'Mic Wireless Rode', 'lokasi' => 'LAB ASM 2', 'kategori' => 'Elektronik', 'stok' => 0, 'icon' => '&#127908;'],
        ];
        foreach ($barangList as $b) {
            Barang::create($b);
        }

        $rahmat = User::create([
            'name' => 'Rahmat Wibowo',
            'username' => 'rahmat',
            'password' => Hash::make('siswa123'),
            'role' => 'siswa',
            'tipe' => 'Siswa',
            'jurusan' => 'RPL – Rekayasa Perangkat Lunak',
            'kelas' => 'XII RPL B',
            'no_wa' => '0821-1409-9231',
            'profile_complete' => true,
        ]);

        $fauzan = User::create([
            'name' => 'Fauzan Kurniawan',
            'username' => 'fauzan',
            'password' => Hash::make('siswa123'),
            'role' => 'siswa',
            'tipe' => 'Siswa',
            'jurusan' => 'ASM – Asisten Manajer',
            'kelas' => 'XI ASM 1',
            'no_wa' => '0812-9988-1177',
            'profile_complete' => true,
        ]);

        Peminjaman::create([
            'user_id' => $rahmat->id,
            'barang_id' => 1,
            'jumlah' => 1,
            'kode_struk' => 'STR-' . random_int(10000, 99999),
            'status' => 'menunggu',
            'tanggal_pengajuan' => now(),
        ]);

        Peminjaman::create([
            'user_id' => $fauzan->id,
            'barang_id' => 4,
            'admin_id' => $admin->id,
            'jumlah' => 1,
            'kode_struk' => 'STR-' . random_int(10000, 99999),
            'status' => 'dipinjam',
            'tanggal_pengajuan' => now()->subDays(3),
            'tanggal_ambil' => now()->subDays(2),
        ]);
    }
}
