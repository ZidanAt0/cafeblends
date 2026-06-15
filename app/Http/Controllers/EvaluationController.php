<?php

namespace App\Http\Controllers;

use App\Services\EvaluationService;

class EvaluationController extends Controller
{
    public function __construct(private EvaluationService $eval) {}

    /** Tampilkan hasil evaluasi seluruh query uji. */
    public function index()
    {
        $result = $this->eval->run();

        return view('evaluation', [
            'rows'    => $result['rows'],
            'summary' => $result['summary'],
        ]);
    }
}
