<?php

namespace App\Services;

use App\Models\Cafe;
use Illuminate\Support\Facades\Storage;

class SearchService
{
    private const INDEX_FILE = 'tfidf_index.json';
    private const MIN_SCORE = 0.0;

    public function __construct(private TextPreprocessor $pre) {}

    public function search(string $query): array
    {
        $startTime = microtime(true);
        $index = $this->loadIndex();

        if (!$index) {
            return [
                'results' => [],
                'elapsed' => 0.0,
                'error' => 'Index belum dibangun. Jalankan: php artisan index:build'
            ];
        }

        $queryTokens = $this->pre->process($query);
        if (count($queryTokens) === 0) {
            return [
                'results' => [],
                'elapsed' => (microtime(true) - $startTime) * 1000,
            ];
        }

        $queryTf = array_count_values($queryTokens);
        $queryVector = [];
        $queryNorm = 0.0;

        foreach ($queryTf as $term => $count) {
            if (!isset($index['idf'][$term])) continue;
            $weight = $count * $index['idf'][$term];
            $queryVector[$term] = $weight;
            $queryNorm += $weight * $weight;
        }
        $queryNorm = sqrt($queryNorm);

        $scores = [];
        foreach ($index['docs'] as $doc) {
            $similarity = $this->cosineSimilarity($queryVector, $doc['vector'], $queryNorm, $doc['norm']);
            if ($similarity >= self::MIN_SCORE) {
                $scores[$doc['id']] = $similarity;
            }
        }

        arsort($scores);

        $results = [];
        foreach ($scores as $cafeId => $score) {
            $cafe = Cafe::find($cafeId);
            if ($cafe) {
                $results[] = ['score' => $score, 'cafe' => $cafe];
            }
        }

        $elapsedMs = (microtime(true) - $startTime) * 1000;
        return ['results' => $results, 'elapsed' => $elapsedMs];
    }

    /**
     * Kembalikan daftar ID cafe terurut dari paling relevan (untuk EVALUASI).
     * Dipakai EvaluationService untuk menghitung Precision/Recall/MAP.
     *
     * @return int[] id cafe terurut menurun berdasarkan skor cosine
     */
    public function rankedIds(string $query, int $limit = 100): array
    {
        $index = $this->loadIndex();
        if (!$index) return [];

        $queryTokens = $this->pre->process($query);
        if (count($queryTokens) === 0) return [];

        $queryTf = array_count_values($queryTokens);
        $queryVector = [];
        $queryNorm = 0.0;
        foreach ($queryTf as $term => $count) {
            if (!isset($index['idf'][$term])) continue;
            $weight = $count * $index['idf'][$term];
            $queryVector[$term] = $weight;
            $queryNorm += $weight * $weight;
        }
        $queryNorm = sqrt($queryNorm);

        $scores = [];
        foreach ($index['docs'] as $doc) {
            $similarity = $this->cosineSimilarity($queryVector, $doc['vector'], $queryNorm, $doc['norm']);
            if ($similarity > 0) {
                $scores[$doc['id']] = $similarity;
            }
        }
        arsort($scores);

        return array_slice(array_keys($scores), 0, $limit);
    }

    private function cosineSimilarity(array $queryVector, array $docVector, float $queryNorm, float $docNorm): float
    {
        if ($queryNorm === 0.0 || $docNorm === 0.0) return 0.0;
        $dotProduct = 0.0;
        foreach ($queryVector as $term => $weight) {
            if (isset($docVector[$term])) $dotProduct += $weight * $docVector[$term];
        }
        return $dotProduct / ($queryNorm * $docNorm);
    }

    private function loadIndex(): ?array
    {
        if (!Storage::exists(self::INDEX_FILE)) return null;
        return json_decode(Storage::get(self::INDEX_FILE), true);
    }
}
