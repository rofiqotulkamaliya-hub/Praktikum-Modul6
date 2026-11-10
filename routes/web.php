<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Kelola\BukuController as KelolaBukuController;
use App\Http\Controllers\Kelola\PeminjamanController as KelolaPeminjamanController;
use App\Http\Controllers\Kelola\PengembalianController as KelolaPengembalianController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PeminjamanSayaController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\SiswaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Halaman publik
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    // Pengguna yang sudah login langsung diarahkan ke dashboard-nya
    return auth()->check() ? redirect()->route('dashboard') : view('home');
})->name('home');

/*
|--------------------------------------------------------------------------
| Halaman yang WAJIB login (middleware auth)
| Pembatasan lebih lanjut memakai middleware role / permission milik Spatie.
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Pintu masuk setelah login -> diarahkan sesuai role
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    // ---- Dashboard per role ----
    Route::get('/admin/dashboard', [AdminController::class, 'index'])
        ->middleware('permission:akses dashboard admin')
        ->name('admin.dashboard');

    Route::get('/petugas/dashboard', [PetugasController::class, 'index'])
        ->middleware('permission:akses dashboard petugas')
        ->name('petugas.dashboard');

    Route::get('/siswa/dashboard', [SiswaController::class, 'index'])
        ->middleware('role:siswa')
        ->name('siswa.dashboard');

    // ---- Kelola data pengguna & role (khusus admin) ----
    Route::middleware('permission:kelola data pengguna')->group(function () {
        Route::resource('admin/users', UserController::class)
            ->except(['show'])
            ->names('admin.users');
    });

    // ---- Daftar & detail buku (semua role) ----
    Route::middleware('permission:lihat daftar buku')->group(function () {
        Route::get('/buku', [BukuController::class, 'index'])->name('buku.index');
        Route::get('/buku/{buku}', [BukuController::class, 'show'])
            ->whereNumber('buku')
            ->name('buku.show');
    });

    // ---- Peminjaman milik sendiri (data difilter berdasarkan user yang login) ----
    Route::post('/peminjaman', [PeminjamanSayaController::class, 'store'])
        ->middleware('permission:mengajukan peminjaman buku')
        ->name('peminjaman.store');

    Route::delete('/peminjaman/{peminjaman}', [PeminjamanSayaController::class, 'batal'])
        ->middleware('permission:mengajukan peminjaman buku')
        ->whereNumber('peminjaman')
        ->name('peminjaman.batal');

    Route::get('/peminjaman-saya', [PeminjamanSayaController::class, 'status'])
        ->middleware('permission:melihat status peminjaman sendiri')
        ->name('peminjaman.status');

    Route::get('/riwayat-peminjaman', [PeminjamanSayaController::class, 'riwayat'])
        ->middleware('permission:melihat riwayat peminjaman sendiri')
        ->name('peminjaman.riwayat');

    Route::get('/peminjaman/{peminjaman}', [PeminjamanSayaController::class, 'show'])
        ->middleware('permission:melihat riwayat peminjaman sendiri')
        ->whereNumber('peminjaman')
        ->name('peminjaman.show');

    // ---- Operasional perpustakaan (admin & petugas) ----
    Route::prefix('kelola')->name('kelola.')->group(function () {

        Route::middleware('permission:kelola data buku')->group(function () {
            Route::resource('buku', KelolaBukuController::class)->except(['show']);
        });

        Route::get('peminjaman', [KelolaPeminjamanController::class, 'index'])
            ->middleware('permission:kelola peminjaman')
            ->name('peminjaman.index');

        Route::post('peminjaman/{peminjaman}/setujui', [KelolaPeminjamanController::class, 'setujui'])
            ->middleware('permission:memproses persetujuan peminjaman')
            ->name('peminjaman.setujui');

        Route::post('peminjaman/{peminjaman}/tolak', [KelolaPeminjamanController::class, 'tolak'])
            ->middleware('permission:memproses persetujuan peminjaman')
            ->name('peminjaman.tolak');

        Route::get('pengembalian', [KelolaPengembalianController::class, 'index'])
            ->middleware('permission:kelola pengembalian')
            ->name('pengembalian.index');

        Route::post('pengembalian/{peminjaman}', [KelolaPengembalianController::class, 'store'])
            ->middleware('permission:mencatat pengembalian buku')
            ->name('pengembalian.store');
    });

    Route::get('/laporan', LaporanController::class)
        ->middleware('permission:lihat laporan perpustakaan')
        ->name('laporan.index');
});

require __DIR__.'/auth.php';
