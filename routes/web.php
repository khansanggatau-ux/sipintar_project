<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventarisController as AdminInventarisController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\PenggunaController;
use App\Http\Controllers\Siswa\PeringatanController;
use App\Http\Controllers\Admin\PeminjamanController as AdminPeminjamanController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\NotifController;
use App\Http\Controllers\Siswa\InventarisController as SiswaInventarisController;
use App\Http\Controllers\Siswa\PengajuanController;
use App\Http\Controllers\Siswa\ProfilController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// ==================== ADMIN ====================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/inventaris', [AdminInventarisController::class, 'index'])->name('inventaris.index');
    Route::post('/inventaris', [AdminInventarisController::class, 'store'])->name('inventaris.store');
    Route::put('/inventaris/{barang}', [AdminInventarisController::class, 'update'])->name('inventaris.update');
    Route::delete('/inventaris/{barang}', [AdminInventarisController::class, 'destroy'])->name('inventaris.destroy');
    Route::post('/inventaris/{barang}/stok', [AdminInventarisController::class, 'tambahStok'])->name('inventaris.stok');

    Route::get('/peminjaman', [AdminPeminjamanController::class, 'index'])->name('peminjaman.index');
    Route::post('/peminjaman/{peminjaman}/approve-ambil', [AdminPeminjamanController::class, 'approveAmbil'])->name('peminjaman.approveAmbil');
    Route::post('/peminjaman/{peminjaman}/approve-kembali', [AdminPeminjamanController::class, 'approveKembali'])->name('peminjaman.approveKembali');
    Route::post('/peminjaman/{peminjaman}/tolak', [AdminPeminjamanController::class, 'tolak'])->name('peminjaman.tolak');
    Route::post('/peminjaman/{peminjaman}/perpanjang-setuju', [AdminPeminjamanController::class, 'perpanjangSetuju'])->name('peminjaman.perpanjangSetuju');
    Route::post('/peminjaman/{peminjaman}/perpanjang-tolak', [AdminPeminjamanController::class, 'perpanjangTolak'])->name('peminjaman.perpanjangTolak');

    Route::get('/pengguna', [PenggunaController::class, 'index'])->name('pengguna.index');
    Route::get('/pengguna/{user}', [PenggunaController::class, 'show'])->name('pengguna.show');
    Route::post('/pengguna/{user}/peringatan', [PenggunaController::class, 'peringatan'])->name('pengguna.peringatan');
    Route::post('/pengguna/{user}/notif', [PenggunaController::class, 'kirimNotif'])->name('pengguna.notif');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/notif', [NotifController::class, 'admin'])->name('notif');
    Route::get('/laporan/cetak', [LaporanController::class, 'cetak'])->name('laporan.cetak');
    Route::get('/laporan/csv', [LaporanController::class, 'csv'])->name('laporan.csv');
});

// ==================== SISWA / GURU ====================
Route::middleware(['auth', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {

    Route::get('/lengkapi-profil', [ProfilController::class, 'lengkapiForm'])->name('lengkapi');
    Route::post('/lengkapi-profil', [ProfilController::class, 'lengkapiSimpan'])->name('lengkapi.simpan');
    Route::get('/notif', [NotifController::class, 'siswa'])->name('notif');
    Route::get('/peringatan', [PeringatanController::class, 'show'])->name('peringatan');
    Route::post('/peringatan/baca', [PeringatanController::class, 'baca'])->name('peringatan.baca');

    Route::middleware('profile.complete')->group(function () {
        Route::get('/inventaris', [SiswaInventarisController::class, 'index'])->name('inventaris.index');
        Route::post('/inventaris/{barang}/ajukan', [SiswaInventarisController::class, 'ajukan'])->name('inventaris.ajukan');
        Route::post('/inventaris/{barang}/kabari', [SiswaInventarisController::class, 'kabari'])->name('inventaris.kabari');

        Route::get('/pengajuan', [PengajuanController::class, 'index'])->name('pengajuan.index');
        Route::delete('/pengajuan/{peminjaman}', [PengajuanController::class, 'batal'])->name('pengajuan.batal');
        Route::post('/pengajuan/{peminjaman}/perpanjang', [PengajuanController::class, 'perpanjang'])->name('pengajuan.perpanjang');

        Route::get('/profil', [ProfilController::class, 'show'])->name('profil');
        Route::post('/profil', [ProfilController::class, 'update'])->name('profil.update');
    });
});