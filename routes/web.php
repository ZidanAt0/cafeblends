<?php

use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routing CafeBlend (hasil integrasi)
|--------------------------------------------------------------------------
| Alur lengkap:
|   '/'           -> SearchController (Angg.3) -> SearchService -> TfidfService
|                    (Angg.2) -> TextPreprocessor (Angg.1), data dari tabel
|                    cafes (Angg.5)
|   '/evaluation' -> EvaluationController (Angg.4) -> EvaluationService
*/
Route::get('/', [SearchController::class, 'index'])->name('search');
Route::get('/evaluation', [EvaluationController::class, 'index'])->name('evaluation');
