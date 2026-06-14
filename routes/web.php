<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routing CafeBlend (Anggota 5)
|--------------------------------------------------------------------------
| Untuk sekarang halaman pencarian hanya menampilkan UI (form + desain),
| belum ada hasil pencarian karena modul pencarian belum diintegrasikan.
|
| Saat INTEGRASI nanti, closure di bawah diganti memanggil SearchController
| (punya Anggota 3) yang menghitung skor asli pakai TF-IDF + Cosine Similarity:
|
|   Route::get('/', [SearchController::class, 'index'])->name('search');
*/
Route::get('/', function () {
    return view('search', [
        'query'   => trim(request('q', '')),
        'results' => [],     // masih kosong, diisi saat integrasi
        'elapsed' => 0.0,
    ]);
})->name('search');
