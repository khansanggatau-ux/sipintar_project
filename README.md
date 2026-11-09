# SIPINTAR - Laravel Version (Overlay)

Ini BUKAN project Laravel yang siap jalan sendiri — ini adalah "isian" (app/, database/,
resources/views/, routes/web.php, public/css & js) yang perlu ditimpakan ke atas project
Laravel kosong yang baru. Ikuti langkah di bawah urut dari atas.

## 1. Buat project Laravel baru

```bash
composer create-project laravel/laravel sipintar "^10.0"
cd sipintar
```

(Butuh PHP >= 8.1, Composer, dan MySQL sudah terpasang di laptop kamu.)

## 2. Timpa file-file berikut dengan isi folder ini

Salin & timpa (overwrite) ke dalam folder project Laravel yang baru dibuat:

```
app/Models/User.php                          -> timpa file bawaan
app/Models/Barang.php                        -> file baru
app/Models/Peminjaman.php                    -> file baru

app/Http/Controllers/Auth/LoginController.php        -> file baru
app/Http/Controllers/Admin/InventarisController.php  -> file baru
app/Http/Controllers/Admin/PeminjamanController.php  -> file baru
app/Http/Controllers/Admin/DashboardController.php   -> file baru
app/Http/Controllers/Siswa/ProfilController.php      -> file baru
app/Http/Controllers/Siswa/InventarisController.php  -> file baru
app/Http/Controllers/Siswa/PengajuanController.php   -> file baru

app/Http/Middleware/EnsureRole.php             -> file baru
app/Http/Middleware/EnsureProfileComplete.php  -> file baru

database/migrations/2024_01_01_000001_create_users_table.php  -> lihat langkah 3
database/migrations/2024_01_01_000002_create_barang_table.php -> file baru
database/migrations/2024_01_01_000003_create_peminjaman_table.php -> file baru
database/seeders/DatabaseSeeder.php            -> timpa file bawaan

resources/views/layouts/guest.blade.php  -> file baru
resources/views/layouts/app.blade.php    -> file baru
resources/views/auth/login.blade.php     -> file baru
resources/views/siswa/*.blade.php        -> file baru (4 file)
resources/views/admin/*.blade.php        -> file baru (3 file)

routes/web.php  -> timpa file bawaan

public/css/style.css  -> file baru
public/js/app.js      -> file baru
```

## 3. PENTING: migration `users` bawaan Laravel

Project Laravel baru sudah punya migration
`database/migrations/0001_01_01_000000_create_users_table.php`. **Hapus file itu**,
lalu pakai punya kita (`2024_01_01_000001_create_users_table.php`) sebagai gantinya,
karena kolomnya beda (ada `username`, `role`, `tipe`, `jurusan`, dst).

Migration bawaan untuk `password_reset_tokens` dan `sessions` (di file yang sama)
boleh dibiarkan / tidak dipakai, tidak masalah.

## 4. Setting `.env`

Buat database MySQL kosong dulu (misal nama `sipintar`), lalu edit `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sipintar
DB_USERNAME=root
DB_PASSWORD=
```

## 5. Daftarkan middleware

Buka `app/Http/Kernel.php`, cari array `$middlewareAliases` (di Laravel versi lama
namanya `$routeMiddleware`), tambahkan dua baris ini:

```php
protected $middlewareAliases = [
    // ...bawaan Laravel di atas, jangan dihapus...
    'role' => \App\Http\Middleware\EnsureRole::class,
    'profile.complete' => \App\Http\Middleware\EnsureProfileComplete::class,
];
```

## 6. Supaya tanggal berbahasa Indonesia

Di `config/app.php`, ubah:
```php
'locale' => 'id',
```

Lalu di `app/Providers/AppServiceProvider.php`, dalam method `boot()`, tambahkan:
```php
\Carbon\Carbon::setLocale(config('app.locale'));
```

## 7. Migrasi & isi data contoh

```bash
php artisan migrate --seed
```

## 8. Jalankan

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000`. Login pakai:
- **Admin**: `admin` / `admin123`
- **Siswa contoh**: `rahmat` / `siswa123` (atau `fauzan` / `siswa123`)
- **Siswa baru**: masukkan username & password bebas, kamu akan diarahkan ke
  halaman "Lengkapi Profil" dulu (sekali saja).

---

## Yang berubah dari versi HTML/localStorage sebelumnya

- Semua data (barang, akun, pengajuan) sekarang di **database MySQL asli**, bukan
  localStorage browser lagi.
- Login pakai **Laravel Auth** beneran (password di-hash pakai bcrypt, session asli),
  bukan simulasi JS.
- Approve Pengambilan & Approve Pengembalian jadi route Laravel terpisah
  (`POST /admin/peminjaman/{id}/approve-ambil` dan `.../approve-kembali`), sesuai
  FR-07 dan FR-08 di dokumen kebutuhanmu.
- Klik kartu barang / baris pengajuan sekarang navigasi lewat query string
  (`?selected=ID`) — server yang render detail panel, bukan lagi JavaScript di
  browser. Ini bikin kodenya lebih gampang kamu kembangin lagi nanti (misal nanti
  mau nambah fitur, tinggal ubah controller & Blade-nya, tanpa perlu ngerti JS
  rumit).
- Modal (Tambah/Edit Barang, Ajukan Pinjam, Struk) masih pakai show/hide via
  `openModal()`/`closeModal()` di `public/js/app.js`, tapi submit form-nya beneran
  ke server (bukan simulasi).
- Tombol "Reset ke Data Awal" di Dashboard sekarang menjalankan
  `php artisan migrate:fresh --seed` di baliknya.

## Belum dikerjakan (silakan dikembangkan sendiri sesuai NFR di dokumenmu)

- Export PDF/Excel (baru tombol placeholder). Bisa pakai `barryvdh/laravel-dompdf`
  dan `maatwebsite/excel`.
- Validasi nomor WhatsApp / format kelas lebih ketat.
- Middleware rate-limiting khusus untuk approve bersamaan (race condition saat
  stok diupdate 2 admin sekaligus) — untuk versi production sebaiknya tambahkan
  `DB::transaction()` + `lockForUpdate()` di `PeminjamanController`.
