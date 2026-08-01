<?php

use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KandidatController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LpjController;
use App\Http\Controllers\PemilihController;
use App\Http\Controllers\PengaduanController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\SuaraController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| PUBLIK (tanpa login)
|--------------------------------------------------------------------------
*/

Route::get('/', [LandingController::class, 'index'])->name('landing');

// GET /login cukup redirect ke landing page, karena form login pemilih
// sudah menyatu di sana (bukan halaman terpisah).
Route::get('/login', function () {
    return redirect()->route('landing');
})->name('login');

Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/pengaduan', [PengaduanController::class, 'create'])->name('pengaduan.create');
Route::post('/pengaduan', [PengaduanController::class, 'store'])->name('pengaduan.store');

/*
|--------------------------------------------------------------------------
| PEMILIH (wajib login guard 'pemilih')
|--------------------------------------------------------------------------
*/

Route::middleware('auth:pemilih')->group(function () {
    Route::get('/voting', [SuaraController::class, 'formVoting'])->name('voting.index');
    Route::post('/voting', [SuaraController::class, 'store'])->name('voting.store');
});

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    Route::middleware('auth:admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Periode -- khusus Ketua Pelaksana + Divisi Teknis & Pemilihan
        // (mereka yang menjalankan operasional harian sistem e-Voting).
        Route::middleware('admin.can:bisaKelolaPeriode')->group(function () {
            Route::get('/periode', [PeriodeController::class, 'index'])->name('periode.index');
            Route::get('/periode/create', [PeriodeController::class, 'create'])->name('periode.create');
            Route::post('/periode', [PeriodeController::class, 'store'])->name('periode.store');
            Route::patch('/periode/{periode}/status', [PeriodeController::class, 'updateStatus'])->name('periode.updateStatus');
            Route::patch('/periode/{periode}/jadwal-voting', [PeriodeController::class, 'setJadwalVoting'])->name('periode.setJadwalVoting');
            Route::patch('/periode/{periode}/toggle-hasil', [PeriodeController::class, 'toggleHasil'])->name('periode.toggleHasil');
        });

        // Kandidat (pasangan calon) -- khusus Ketua Pelaksana + Divisi
        // Regulasi & Verifikasi (mereka yang menetapkan pasangan calon).
        Route::middleware('admin.can:bisaKelolaKandidat')->group(function () {
            Route::get('/periode/{periode}/kandidat', [KandidatController::class, 'index'])->name('kandidat.index');
            Route::post('/periode/{periode}/kandidat', [KandidatController::class, 'store'])->name('kandidat.store');
            Route::patch('/periode/{periode}/kandidat/{kandidat}', [KandidatController::class, 'update'])->name('kandidat.update');
            Route::delete('/periode/{periode}/kandidat/{kandidat}', [KandidatController::class, 'destroy'])->name('kandidat.destroy');
        });

        // Pemilih (akun terkunci + log presensi) -- khusus Ketua Pelaksana +
        // Divisi Teknis & Pemilihan (mereka yang mengelola presensi pemilih).
        Route::middleware('admin.can:bisaKelolaPemilih')->group(function () {
            Route::get('/periode/{periode}/pemilih-terkunci', [PemilihController::class, 'terkunci'])->name('pemilih.terkunci');
            // Route::patch('/periode/{periode}/pemilih/{pemilih}/unlock', [PemilihController::class, 'unlock'])->name('pemilih.unlock');
            Route::get('/periode/{periode}/presensi', [AuditController::class, 'presensi'])->name('audit.presensi');
            Route::get('/pengaduan', [PengaduanController::class, 'index'])->name('pengaduan.index');
            Route::get('/pengaduan/{pengaduan}', [PengaduanController::class, 'show'])->name('pengaduan.show');
            Route::patch('/pengaduan/{pengaduan}/setujui', [PengaduanController::class, 'setujui'])->name('pengaduan.setujui');
            Route::patch('/pengaduan/{pengaduan}/tolak', [PengaduanController::class, 'tolak'])->name('pengaduan.tolak');
        });

        // Audit log -- khusus Ketua Pelaksana (wakil sementara superadmin).
        Route::middleware('admin.can:bisaLihatAuditLog')->group(function () {
            Route::get('/audit-log', [AuditController::class, 'index'])->name('audit.index');
        });

        // LPJ -- isi (semua divisi kecuali Ketua) vs review (khusus Ketua).
        Route::middleware('admin.can:bisaIsiLpj')->group(function () {
            Route::get('/periode/{periode}/lpj', [LpjController::class, 'edit'])->name('lpj.edit');
            Route::put('/periode/{periode}/lpj', [LpjController::class, 'update'])->name('lpj.update');
        });

        Route::middleware('admin.can:bisaReviewLpj')->group(function () {
            Route::get('/periode/{periode}/lpj-review', [LpjController::class, 'index'])->name('lpj.index');
            Route::get('/periode/{periode}/lpj-review/export-semua', [LpjController::class, 'exportSemua'])->name('lpj.exportSemua');
            Route::get('/periode/{periode}/lpj-review/{lpj}', [LpjController::class, 'show'])->name('lpj.show');
            Route::get('/periode/{periode}/lpj-review/{lpj}/export', [LpjController::class, 'export'])->name('lpj.export');
            Route::patch('/periode/{periode}/lpj-review/{lpj}/sahkan', [LpjController::class, 'sahkan'])->name('lpj.sahkan');
            Route::patch('/periode/{periode}/lpj-review/{lpj}/minta-revisi', [LpjController::class, 'mintaRevisi'])->name('lpj.mintaRevisi');
        });
    });
});
