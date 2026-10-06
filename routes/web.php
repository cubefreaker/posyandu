<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirect root to dashboard / pelayanan
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->role === 'kader' ? 'pelayanan.index' : 'dashboard');
    }
    return redirect()->route('login');
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::livewire('/dashboard', 'dashboard')->name('dashboard');
    Route::livewire('/pelayanan', 'pelayanan.index')->name('pelayanan.index');

    // Data Ibu
    Route::livewire('/data-ibu', 'data-ibu.index')->name('data-ibu.index');
    Route::livewire('/data-ibu/tambah', 'data-ibu.form')->name('data-ibu.create');
    Route::livewire('/data-ibu/{id}/edit', 'data-ibu.form')->name('data-ibu.edit');

    // Kesehatan Ibu Hamil (Buku KIA)
    Route::livewire('/kesehatan-ibu', 'kesehatan-ibu.index')->name('kesehatan-ibu.index');
    Route::livewire('/kesehatan-ibu/tambah', 'kesehatan-ibu.form-kehamilan')->name('kesehatan-ibu.create');
    Route::livewire('/kesehatan-ibu/{id}/edit', 'kesehatan-ibu.form-kehamilan')->name('kesehatan-ibu.edit');
    Route::livewire('/kesehatan-ibu/{kehamilanId}/periksa', 'kesehatan-ibu.periksa')->name('kesehatan-ibu.periksa');
    Route::livewire('/kesehatan-ibu/{kehamilanId}/grafik', 'kesehatan-ibu.grafik')->name('kesehatan-ibu.grafik');

    // Data Anak
    Route::livewire('/data-anak', 'data-anak.index')->name('data-anak.index');
    Route::livewire('/data-anak/tambah', 'data-anak.form')->name('data-anak.create');
    Route::livewire('/data-anak/{id}/edit', 'data-anak.form')->name('data-anak.edit');

    // Penimbangan
    Route::livewire('/penimbangan', 'penimbangan.index')->name('penimbangan.index');
    Route::livewire('/penimbangan/tambah', 'penimbangan.form')->name('penimbangan.create');
    Route::livewire('/penimbangan/{id}/edit', 'penimbangan.form')->name('penimbangan.edit');
    Route::livewire('/penimbangan/grafik/{anakId}', 'penimbangan.grafik-kms')->name('penimbangan.grafik');

    // Imunisasi
    Route::livewire('/imunisasi', 'imunisasi.index')->name('imunisasi.index');
    Route::livewire('/imunisasi/tambah', 'imunisasi.form')->name('imunisasi.create');
    Route::livewire('/imunisasi/{id}/edit', 'imunisasi.form')->name('imunisasi.edit');

    // Vitamin
    Route::livewire('/vitamin', 'vitamin.index')->name('vitamin.index');
    Route::livewire('/vitamin/tambah', 'vitamin.form')->name('vitamin.create');
    Route::livewire('/vitamin/{id}/edit', 'vitamin.form')->name('vitamin.edit');

    // Laporan
    Route::livewire('/laporan', 'laporan.index')->name('laporan.index');
    Route::get('/laporan/export-pdf', [LaporanController::class, 'exportPdf'])->name('laporan.export-pdf');

    // Manajemen User (Admin Only)
    Route::middleware(\App\Http\Middleware\IsAdmin::class)->group(function () {
        Route::livewire('/user-management', 'user-management.index')->name('user-management.index');
        Route::livewire('/user-management/tambah', 'user-management.form')->name('user-management.create');
        Route::livewire('/user-management/{id}/edit', 'user-management.form')->name('user-management.edit');
    });
});

// route for php artisan optimize clear 
Route::get('/optimize-clear', function () {
    Artisan::call('optimize:clear');
    return redirect()->back()->with('success', 'Optimize clear berhasil!');
})->name('optimize-clear');

// route for storage link
Route::get('/storage-link', function () {
    Artisan::call('storage:link');
    return redirect()->back()->with('success', 'Storage link berhasil!');
})->name('storage-link');