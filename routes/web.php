<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redirect root to dashboard
Route::get('/', fn () => redirect()->route('dashboard'));

// Protected routes
Route::middleware('auth')->group(function () {
    Route::livewire('/dashboard', 'dashboard')->name('dashboard');

    // Data Ibu
    Route::livewire('/data-ibu', 'data-ibu.index')->name('data-ibu.index');
    Route::livewire('/data-ibu/tambah', 'data-ibu.form')->name('data-ibu.create');
    Route::livewire('/data-ibu/{id}/edit', 'data-ibu.form')->name('data-ibu.edit');

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
});