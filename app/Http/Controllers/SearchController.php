<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(private SearchService $search) {}

    /**
     * Tampilkan halaman pencarian dengan hasil (jika ada query).
     */
    public function index(Request $request)
    {
        $query = trim($request->get('q', ''));
        $results = [];
        $elapsed = 0.0;
        $error = null;

        if ($query !== '') {
            $searchResult = $this->search->search($query);
            $results = $searchResult['results'] ?? [];
            $elapsed = $searchResult['elapsed'] ?? 0.0;
            $error = $searchResult['error'] ?? null;
        }

        return view('search', [
            'query'   => $query,
            'results' => $results,
            'elapsed' => $elapsed,
            'error'   => $error,
        ]);
    }
}
