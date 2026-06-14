<?php

use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routing CafeBlend
|--------------------------------------------------------------------------
| Route pencarian menggunakan SearchController yang menggabungkan:
| - TextPreprocessor (Anggota 2): preprocessing query
| - TfidfService (Anggota 3): indeks TF-IDF
| - SearchService (Anggota 3): cosine similarity & ranking
| - UI/Database (Anggota 5): halaman dan data
*/

Route::get('/', [SearchController::class, 'index'])->name('search');
