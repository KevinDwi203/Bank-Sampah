<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NasabahController;
use App\Http\Controllers\JenisSampahController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\PenarikanController;
use App\Http\Controllers\TabunganController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ArusKasController;
use App\Http\Controllers\AkunSayaController;

// Setup & Migrasi helper (tanpa perlu SSH/Shell)
Route::get('/run-migrate-setup', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        return '<div style="font-family:sans-serif;padding:30px;text-align:center;">
            <h1 style="color:#10b981;">✅ Sukses!</h1>
            <p style="font-size:16px;">Database berhasil di-migrate dan di-seed dengan data awal.</p>
            <p><a href="/login" style="display:inline-block;padding:10px 20px;background:#10b981;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;">Pergi ke Halaman Login</a></p>
        </div>';
    } catch (\Throwable $e) {
        return '<div style="font-family:sans-serif;padding:30px;color:#ef4444;">
            <h1>❌ Gagal Menjalankan Migrasi</h1>
            <p>' . htmlspecialchars($e->getMessage()) . '</p>
        </div>';
    }
});

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    // ===== Semua role yang sudah login =====
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::get('/', fn() => redirect(auth()->user()->homeRoute()));

    // ===== Nasabah saja =====
    Route::middleware('role:Nasabah')->group(function () {
        Route::get('/akun-saya', [AkunSayaController::class, 'index'])->name('akun.saya');
    });

    // ===== Administrator + Petugas =====
    Route::middleware('role:Administrator,Petugas')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Nasabah
        Route::resource('nasabah', NasabahController::class);

        // Transaksi / Setor Sampah ("transaksi/batch" harus sebelum resource)
        Route::delete('transaksi/batch', [TransaksiController::class, 'destroyBatch'])->name('riwayat.batch.destroy');
        Route::resource('transaksi', TransaksiController::class)
            ->except(['edit', 'update'])
            ->names([
                'index' => 'riwayat.index',
                'create' => 'riwayat.create',
                'store' => 'riwayat.store',
                'show' => 'riwayat.show',
                'destroy' => 'riwayat.destroy',
            ]);
        Route::put('transaksi/{transaksi}/status', [TransaksiController::class, 'updateStatus'])->name('riwayat.updateStatus');

        // Arus Kas: Petugas hanya boleh melihat dan menambah
        Route::get('arus-kas', [ArusKasController::class, 'index'])->name('arus-kas.index');
        Route::get('arus-kas/create', [ArusKasController::class, 'create'])->name('arus-kas.create');
        Route::post('arus-kas', [ArusKasController::class, 'store'])->name('arus-kas.store');
    });

    // ===== Administrator saja =====
    Route::middleware('role:Administrator')->group(function () {
        Route::resource('jenis-sampah', JenisSampahController::class)
            ->except(['show'])
            ->parameters(['jenis-sampah' => 'jenisSampah']);

        Route::resource('penarikan', PenarikanController::class)->except(['edit', 'update']);
        Route::put('penarikan/{penarikan}/status', [PenarikanController::class, 'updateStatus'])->name('penarikan.updateStatus');

        Route::resource('tabungan', TabunganController::class)
            ->only(['index', 'show'])
            ->parameters(['tabungan' => 'nasabah']);

        Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('laporan/mingguan', [LaporanController::class, 'mingguan'])->name('laporan.mingguan');
        Route::get('laporan/bulanan', [LaporanController::class, 'bulanan'])->name('laporan.bulanan');
        Route::get('laporan/kiloan', [LaporanController::class, 'kiloan'])->name('laporan.kiloan');
        Route::get('laporan/pdf/mingguan', [LaporanController::class, 'pdfMingguan'])->name('laporan.pdf.mingguan');
        Route::get('laporan/pdf/bulanan', [LaporanController::class, 'pdfBulanan'])->name('laporan.pdf.bulanan');
        Route::get('laporan/pdf/kiloan', [LaporanController::class, 'pdfKiloan'])->name('laporan.pdf.kiloan');
        Route::get('laporan/pdf/rekap', [LaporanController::class, 'pdfRekap'])->name('laporan.pdf.rekap');

        Route::get('setting', [SettingController::class, 'index'])->name('setting.index');
        Route::post('setting', [SettingController::class, 'update'])->name('setting.update');
        Route::delete('setting/logo', [SettingController::class, 'destroyLogo'])->name('setting.logo.destroy');

        Route::resource('users', UserController::class)->except(['show']);
        // Arus Kas: ubah & hapus khusus Administrator
        Route::resource('arus-kas', ArusKasController::class)
            ->only(['edit', 'update', 'destroy'])
            ->parameters(['arus-kas' => 'arusKas']);
    });
});