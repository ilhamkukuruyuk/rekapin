<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashPaymentController;
use App\Http\Controllers\CashReportController;
use App\Http\Controllers\CashTransactionController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\PiketController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\PresensiRekapController;
use Illuminate\Support\Facades\Route;

// Redirect root ke dashboard atau login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect(auth()->user()->dashboard_route);
    }

    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');

    // Keep legacy login URLs working while serving a single login page.
    Route::redirect('/anggota/login', '/login')->name('anggota.login');
    Route::redirect('/bendahara/login', '/login')->name('bendahara.login');
    Route::redirect('/psdi/login', '/login')->name('psdi.login');
    Route::redirect('/presbem/login', '/login')->name('presbem.login');

    // Login Submission
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.post');
});

// Logout Route
Route::match(['get'], '/logout', function () {
    abort(405);
});
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware(['auth', 'idle'])
    ->name('logout');

// Update Profile Route (for all authenticated users)
Route::post('/profile/update', [AccountController::class, 'updateProfile'])
    ->middleware(['auth', 'idle'])
    ->name('profile.update');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'idle', 'role:admin'])
    ->group(function () {
        Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create');
        Route::post('/accounts', [AccountController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('accounts.store');
    });

// ==========================================
// 1. ANGGOTA ROUTES (Role: anggota)
// ==========================================
Route::prefix('anggota')
    ->name('anggota.')
    ->middleware(['auth', 'idle', 'role:anggota'])
    ->group(function () {
        Route::get('/dashboard', [CashPaymentController::class, 'memberDashboard'])->name('dashboard');

        Route::get('/bayar-kas', [CashPaymentController::class, 'memberIndex'])->name('bayar-kas');
        Route::post('/bayar-kas', [CashPaymentController::class, 'submit'])->name('bayar-kas.submit');

        Route::get('/riwayat-pembayaran', [CashPaymentController::class, 'memberHistory'])->name('riwayat-pembayaran');

        Route::get('/jadwal-piket', [PiketController::class, 'memberIndex'])->name('jadwal-piket');
        Route::post('/jadwal-piket/{schedule}/presensi', [PiketController::class, 'attend'])->name('jadwal-piket.presensi');
        Route::get('/jadwal-piket/{schedule}/bukti', [PiketController::class, 'evidence'])->name('jadwal-piket.bukti');

        Route::get('/presensi-kegiatan', [PresensiController::class, 'index'])->name('presensi-kegiatan');
        Route::post('/presensi-kegiatan', [PresensiController::class, 'store'])->name('presensi-kegiatan.store');
        Route::post('/presensi-kegiatan/izin', [PresensiController::class, 'ajukanIzin'])->name('presensi-kegiatan.izin');
        Route::get('/presensi-kegiatan/bukti/{presensi}', [PresensiController::class, 'bukti'])->name('presensi-kegiatan.bukti');
        Route::get('/presensi-kegiatan/izin/{izin}/bukti', [PresensiController::class, 'buktiIzin'])->name('presensi-kegiatan.izin.bukti');

        Route::get('/riwayat-presensi', [PresensiController::class, 'history'])->name('riwayat-presensi');

        Route::get('/denda', [CashPaymentController::class, 'memberFines'])->name('denda');

        Route::get('/transparansi-kas', [CashTransactionController::class, 'transparansiKas'])->name('transparansi-kas');

        Route::get('/profile', [CashPaymentController::class, 'memberProfile'])->name('profile');
    });

// ==========================================
// 2. BENDAHARA / ADKEU ROUTES (Role: bendahara, adkeu)
// ==========================================
Route::prefix('bendahara')
    ->name('bendahara.')
    ->middleware(['auth', 'idle', 'role:bendahara,adkeu'])
    ->group(function () {
        Route::get('/dashboard', [CashReportController::class, 'dashboard'])->name('dashboard');

        Route::get('/kas', [CashTransactionController::class, 'index'])->name('kas');
        Route::post('/kas/transaksi', [CashTransactionController::class, 'store'])->name('kas.store');
        Route::put('/kas/transaksi/{transaction}', [CashTransactionController::class, 'update'])->name('kas.update');
        Route::delete('/kas/transaksi/{transaction}', [CashTransactionController::class, 'destroy'])->name('kas.destroy');

        Route::get('/validasi-pembayaran', [CashPaymentController::class, 'reviewIndex'])->name('validasi-pembayaran');
        Route::post('/validasi-pembayaran/approve', [CashPaymentController::class, 'approveMultiple'])->name('validasi-pembayaran.approve-multiple');
        Route::get('/validasi-pembayaran/{payment}/bukti', [CashPaymentController::class, 'evidence'])->name('validasi-pembayaran.evidence');
        Route::post('/validasi-pembayaran/{payment}/approve', [CashPaymentController::class, 'approve'])->name('validasi-pembayaran.approve');
        Route::post('/validasi-pembayaran/{payment}/reject', [CashPaymentController::class, 'reject'])->name('validasi-pembayaran.reject');

        Route::get('/rekap-kas', [CashReportController::class, 'index'])->name('rekap-kas');
        Route::get('/rekap-kas/export', [CashReportController::class, 'export'])->name('rekap-kas.export');

        Route::get('/denda', function () {
            return view('bendahara.denda');
        })->name('denda');

        Route::get('/jadwal-piket', [PiketController::class, 'managementIndex'])->name('jadwal-piket');
        Route::post('/jadwal-piket', [PiketController::class, 'storeSchedule'])->name('jadwal-piket.store');
        Route::get('/jadwal-piket/{schedule}/bukti', [PiketController::class, 'evidence'])->name('jadwal-piket.bukti');

        Route::get('/presensi-piket', [PiketController::class, 'attendanceIndex'])->name('presensi-piket');

        Route::get('/rekap-presensi', [PresensiRekapController::class, 'index'])->name('rekap-presensi');
        Route::get('/rekap-presensi/export', [PresensiRekapController::class, 'export'])->name('rekap-presensi.export');

        Route::get('/profile', function () {
            return view('bendahara.profile');
        })->name('profile');
    });

Route::prefix('api/bendahara')
    ->name('api.bendahara.')
    ->middleware(['auth', 'idle', 'role:bendahara,adkeu'])
    ->group(function () {
        Route::get('/transaksi-kas', [CashTransactionController::class, 'index'])->name('transaksi-kas.index');
        Route::post('/transaksi-kas', [CashTransactionController::class, 'store'])->name('transaksi-kas.store');
        Route::put('/transaksi-kas/{transaction}', [CashTransactionController::class, 'update'])->name('transaksi-kas.update');
        Route::delete('/transaksi-kas/{transaction}', [CashTransactionController::class, 'destroy'])->name('transaksi-kas.destroy');
    });

// ==========================================
// 3. PSDI ROUTES (Role: psdi)
// ==========================================
Route::prefix('psdi')
    ->name('psdi.')
    ->middleware(['auth', 'idle', 'role:psdi'])
    ->group(function () {
        Route::get('/dashboard', [KegiatanController::class, 'dashboard'])->name('dashboard');

        Route::get('/kegiatan', [KegiatanController::class, 'index'])->name('kegiatan');
        Route::post('/kegiatan', [KegiatanController::class, 'store'])->name('kegiatan.store');
        Route::post('/kegiatan/{kegiatan}/sesi/buka', [KegiatanController::class, 'openSession'])->name('kegiatan.sesi.buka');
        Route::post('/kegiatan/{kegiatan}/sesi/tutup', [KegiatanController::class, 'closeSession'])->name('kegiatan.sesi.tutup');

        Route::get('/rekap-presensi', [PresensiRekapController::class, 'index'])->name('rekap-presensi');
        Route::get('/rekap-presensi/export', [PresensiRekapController::class, 'export'])->name('rekap-presensi.export');

        Route::get('/perizinan', [KegiatanController::class, 'perizinan'])->name('perizinan');
        Route::post('/perizinan/{izin}/review', [KegiatanController::class, 'reviewIzin'])->name('perizinan.review');
        Route::get('/perizinan/{izin}/bukti', [PresensiController::class, 'buktiIzin'])->name('perizinan.bukti');

        Route::get('/profile', function () {
            return view('psdi.profile');
        })->name('profile');
    });

// ==========================================
// 4. PRESBEM ROUTES (Role: presbem)
// ==========================================
Route::prefix('presbem')
    ->name('presbem.')
    ->middleware(['auth', 'idle', 'role:presbem'])
    ->group(function () {
        Route::get('/dashboard', [CashReportController::class, 'dashboard'])->name('dashboard');

        Route::get('/bayar-kas', [CashPaymentController::class, 'memberIndex'])->name('bayar-kas');
        Route::post('/bayar-kas', [CashPaymentController::class, 'submit'])->name('bayar-kas.submit');

        Route::get('/riwayat-pembayaran', [CashPaymentController::class, 'memberHistory'])->name('riwayat-pembayaran');

        Route::get('/jadwal-piket', [PiketController::class, 'memberIndex'])->name('jadwal-piket');
        Route::post('/jadwal-piket/{schedule}/presensi', [PiketController::class, 'attend'])->name('jadwal-piket.presensi');
        Route::get('/jadwal-piket/{schedule}/bukti', [PiketController::class, 'evidence'])->name('jadwal-piket.bukti');

        Route::get('/presensi-kegiatan', [PresensiController::class, 'index'])->name('presensi-kegiatan');
        Route::post('/presensi-kegiatan', [PresensiController::class, 'store'])->name('presensi-kegiatan.store');
        Route::post('/presensi-kegiatan/izin', [PresensiController::class, 'ajukanIzin'])->name('presensi-kegiatan.izin');
        Route::get('/presensi-kegiatan/bukti/{presensi}', [PresensiController::class, 'bukti'])->name('presensi-kegiatan.bukti');
        Route::get('/presensi-kegiatan/izin/{izin}/bukti', [PresensiController::class, 'buktiIzin'])->name('presensi-kegiatan.izin.bukti');

        Route::get('/riwayat-presensi', [PresensiController::class, 'history'])->name('riwayat-presensi');

        Route::get('/denda', [CashPaymentController::class, 'memberFines'])->name('denda');

        Route::get('/transparansi-kas', [CashTransactionController::class, 'transparansiKas'])->name('transparansi-kas');

        Route::get('/rekap-kas', [CashReportController::class, 'index'])->name('rekap-kas');
        Route::get('/rekap-kas/export', [CashReportController::class, 'export'])->name('rekap-kas.export');

        Route::get('/rekap-presensi', [PresensiRekapController::class, 'index'])->name('rekap-presensi');
        Route::get('/rekap-presensi/export', [PresensiRekapController::class, 'export'])->name('rekap-presensi.export');

        Route::get('/profile', function () {
            return view('presbem.profile');
        })->name('profile');
    });
