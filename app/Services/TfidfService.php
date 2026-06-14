<?php

namespace App\Services;

use App\Models\Cafe;
use Illuminate\Support\Facades\Storage;

class TfidfService
{
    private const INDEX_FILE = 'tfidf_index.json';

    public function __construct(private TextPreprocessor $pre) {}

    public function build(): array
    {
        $cafes = Cafe::all();
        $n = $cafes->count();

        $docTermFreq = [];
        $docFreq = [];
        $meta = [];

        foreach ($cafes as $cafe) {
            $tokens = $this->pre->process($cafe->review_text);
            $tf = array_count_values($tokens);
            $docTermFreq[$cafe->id] = $tf;
            $meta[$cafe->id] = ['id' => $cafe->id, 'name' => $cafe->name];

            foreach (array_keys($tf) as $term) {
                $docFreq[$term] = ($docFreq[$term] ?? 0) + 1;
            }
        }

        $idf = [];
        foreach ($docFreq as $term => $df) {
            $idf[$term] = log($n / $df);
        }

        $docs = [];
        foreach ($docTermFreq as $docId => $tf) {
            $vector = [];
            $sumSquares = 0.0;
            foreach ($tf as $term => $count) {
                $weight = $count * $idf[$term];
                if ($weight != 0.0) {
                    $vector[$term] = $weight;
                    $sumSquares += $weight * $weight;
                }
            }
            $docs[] = [
                'id'     => $meta[$docId]['id'],
                'name'   => $meta[$docId]['name'],
                'vector' => $vector,
                'norm'   => sqrt($sumSquares),
            ];
        }

        $index = [
            'N'        => $n,
            'vocab'    => count($idf),
            'idf'      => $idf,
            'docs'     => $docs,
            'built_at' => now()->toDateTimeString(),
        ];
        Storage::put(self::INDEX_FILE, json_encode($index));

        return [
            'documents' => $n,
            'vocabulary' => count($idf),
        ];
    }

    public function load(): ?array
    {
        if (!Storage::exists(self::INDEX_FILE)) {
            return null;
        }
        return json_decode(Storage::get(self::INDEX_FILE), true);
    }
}
