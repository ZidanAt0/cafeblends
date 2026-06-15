<?php

namespace App\Services;

/**
 * EvaluationService
 * --------------------------------------------------------------------------
 * Mengevaluasi kualitas ranking sistem dengan membandingkan hasil pencarian
 * terhadap GROUND TRUTH (daftar cafe yang dianggap relevan untuk tiap query).
 *
 * Ground truth disusun dari keberadaan kata kunci konsep pada teks review asli
 * (mis. query "rooftop" -> cafe yang review-nya benar membahas rooftop/terrace).
 * Jadi ground truth = penilaian relevansi manual berbasis isi, sedangkan sistem
 * menilai lewat TF-IDF + cosine. Mekanismenya berbeda, sehingga metrik bermakna.
 *
 * Metrik:
 *   - Precision@K : dari K hasil teratas, berapa persen yang relevan
 *   - Recall      : dari semua dok relevan, berapa yang berhasil ditemukan
 *   - AP / MAP    : Average Precision per query, dirata-rata jadi MAP
 */
class EvaluationService
{
    /** Banyaknya hasil teratas yang dinilai untuk Precision@K. */
    public const K = 10;

    public function __construct(private SearchService $search) {}

    /**
     * 13 query uji (konteks berbeda) + ground truth (id cafe relevan).
     * @return array<int, array{query:string, relevant:int[]}>
     */
    public function testCases(): array
    {
        return [
            ['query' => 'good coffee espresso latte',          'relevant' => [6,7,9,10,13,15,17,18,19,21,24,25,39,28,30,31,32,35,36,44,46,47,49,50,51,53,54,57,58,59,60,61,63,64,67,71,72,74]],
            ['query' => 'sweet dessert cake and pastry',       'relevant' => [2,3,5,9,10,11,12,13,14,15,23,39,28,29,30,33,36,42,43,44,46,47,50,52,57,60,61,62,64,67,70,72]],
            ['query' => 'pizza italian food',                  'relevant' => [2,6,7,12,13,16,39,29,30,33,36,41,44,46,48,50,51,52,53,62,70,73]],
            ['query' => 'rooftop cafe with terrace',           'relevant' => [12,14,27,37,45]],
            ['query' => 'breakfast brunch in the morning',     'relevant' => [18,25,27,34,41,43]],
            ['query' => 'cozy aesthetic ambience interior',    'relevant' => [4,5,6,7,11,12,13,14,15,17,18,20,23,27,39,28,29,30,40,33,34,35,36,38,43,44,45,48,50,51,52,53,54,57,58,59,60,61,62,64,65,66,67,69,70,72,73]],
            ['query' => 'friendly service polite staff',       'relevant' => [2,6,8,10,11,12,14,15,22,25,27,39,29,30,32,33,34,36,38,41,43,45,46,48,49,51,52,53,54,55,57,59,60,62,63,64,66,69,70,71,72,74]],
            ['query' => 'outdoor garden lake view',            'relevant' => [6,8,11,35,38,41,43,44,45,46,50,51,63,67]],
            ['query' => 'burger and sandwich',                 'relevant' => [2,7,9,10,11,15,17,19,22,25,27,30,31,40,34,36,44,51,52,53,57,66,67,71]],
            ['query' => 'milkshake smoothie cold beverage',    'relevant' => [1,2,7,8,9,10,13,14,21,28,29,30,33,36,37,44,46,49,50,52,53,57,59,64,67,70,71]],
            ['query' => 'pasta and noodles',                   'relevant' => [2,6,12,14,23,39,29,32,36,37,38,44,48,49,50,51,52,53,54,56,61,64,68,69,70,72,73]],
            ['query' => 'place for family and friends',        'relevant' => [1,2,5,8,9,10,11,13,14,15,20,22,23,39,33,35,38,45,49,50,57,58,62,65,67]],
            ['query' => 'affordable price value for money',    'relevant' => [4,8,9,13,14,15,23,27,39,33,34,36,46,50,53,63,64,65,66,67,69]],
        ];
    }

    /** Jalankan evaluasi seluruh query, kembalikan rincian + ringkasan. */
    public function run(): array
    {
        $rows = [];
        $sumP = 0.0; $sumR = 0.0; $sumAP = 0.0;
        $cases = $this->testCases();

        foreach ($cases as $case) {
            $relevant = array_flip($case['relevant']);        // set utk lookup O(1)
            $ranked   = $this->search->rankedIds($case['query'], 100); // hasil terurut

            $p   = $this->precisionAtK($ranked, $relevant, self::K);
            $r   = $this->recall($ranked, $relevant);
            $ap  = $this->averagePrecision($ranked, $relevant);

            $rows[] = [
                'query'        => $case['query'],
                'relevant_total' => count($case['relevant']),
                'retrieved'    => count($ranked),
                'precision'    => $p,
                'recall'       => $r,
                'ap'           => $ap,
            ];
            $sumP += $p; $sumR += $r; $sumAP += $ap;
        }

        $count = count($cases);
        return [
            'rows'    => $rows,
            'summary' => [
                'k'             => self::K,
                'queries'       => $count,
                'avg_precision' => $sumP / $count,   // rata-rata Precision@K
                'avg_recall'    => $sumR / $count,
                'map'           => $sumAP / $count,  // Mean Average Precision
            ],
        ];
    }

    /** Precision@K = (relevan dalam K teratas) / K */
    private function precisionAtK(array $ranked, array $relevantSet, int $k): float
    {
        $topK = array_slice($ranked, 0, $k);
        $hit = 0;
        foreach ($topK as $id) {
            if (isset($relevantSet[$id])) $hit++;
        }
        return $k > 0 ? $hit / $k : 0.0;
    }

    /** Recall = (relevan yang ditemukan) / (total relevan) */
    private function recall(array $ranked, array $relevantSet): float
    {
        $total = count($relevantSet);
        if ($total === 0) return 0.0;
        $hit = 0;
        foreach ($ranked as $id) {
            if (isset($relevantSet[$id])) $hit++;
        }
        return $hit / $total;
    }

    /**
     * Average Precision: rata-rata precision pada tiap posisi dokumen relevan
     * ditemukan. Menghargai sistem yang menaruh dok relevan di urutan atas.
     */
    private function averagePrecision(array $ranked, array $relevantSet): float
    {
        $total = count($relevantSet);
        if ($total === 0) return 0.0;

        $hit = 0;
        $sumPrec = 0.0;
        foreach ($ranked as $i => $id) {
            if (isset($relevantSet[$id])) {
                $hit++;
                $sumPrec += $hit / ($i + 1);   // precision pada rank ke-(i+1)
            }
        }
        return $hit > 0 ? $sumPrec / min($total, count($ranked)) : 0.0;
    }
}
