<?php

namespace App\Services;

use App\Models\Cafe;
use Illuminate\Support\Facades\Storage;

/**
 * TfidfService
 * --------------------------------------------------------------------------
 * Bertugas membangun INDEX TF-IDF dari seluruh dokumen (cafe) lalu
 * menyimpannya ke file. Index inilah yang dipakai SearchService saat mencari,
 * sehingga pencarian tidak perlu menghitung ulang dari awal setiap kali.
 *
 * Isi index yang disimpan:
 *   - N    : jumlah dokumen
 *   - idf  : nilai IDF tiap term  (term => idf)
 *   - docs : tiap dokumen -> { id, name, vector (term=>bobot tfidf), norm }
 */
class TfidfService
{
    private const INDEX_FILE = 'tfidf_index.json';

    public function __construct(private TextPreprocessor $pre) {}

    /**
     * Bangun index dari tabel cafes lalu simpan ke storage/app/tfidf_index.json
     *
     * @return array ringkasan statistik (untuk ditampilkan ke command)
     */
    public function build(): array
    {
        $cafes = Cafe::all();
        $n = $cafes->count();

        // --- 1) Hitung TF tiap dokumen + DF tiap term -----------------------
        $docTermFreq = [];   // [docId => [term => count]]
        $docFreq     = [];   // [term => jumlah dokumen yang memuatnya]
        $meta        = [];   // [docId => ['id','name']]

        foreach ($cafes as $cafe) {
            $tokens = $this->pre->process($cafe->review_text);

            $tf = array_count_values($tokens);   // hitung frekuensi tiap term
            $docTermFreq[$cafe->id] = $tf;
            $meta[$cafe->id] = ['id' => $cafe->id, 'name' => $cafe->name];

            // tiap term unik di dokumen ini menambah document frequency-nya
            foreach (array_keys($tf) as $term) {
                $docFreq[$term] = ($docFreq[$term] ?? 0) + 1;
            }
        }

        // --- 2) Hitung IDF tiap term: log(N / df) ---------------------------
        $idf = [];
        foreach ($docFreq as $term => $df) {
            $idf[$term] = log($n / $df);
        }

        // --- 3) Hitung vektor TF-IDF tiap dokumen + panjang vektor (norm) ---
        $docs = [];
        foreach ($docTermFreq as $docId => $tf) {
            $vector = [];
            $sumSquares = 0.0;
            foreach ($tf as $term => $count) {
                $weight = $count * $idf[$term];   // bobot = TF * IDF
                if ($weight != 0.0) {
                    $vector[$term] = $weight;
                    $sumSquares += $weight * $weight;
                }
            }
            $docs[] = [
                'id'     => $meta[$docId]['id'],
                'name'   => $meta[$docId]['name'],
                'vector' => $vector,
                'norm'   => sqrt($sumSquares),     // |d| untuk cosine
            ];
        }

        // --- 4) Simpan index ke file ----------------------------------------
        $index = [
            'N'         => $n,
            'vocab'     => count($idf),
            'idf'       => $idf,
            'docs'      => $docs,
            'built_at'  => now()->toDateTimeString(),
        ];
        Storage::put(self::INDEX_FILE, json_encode($index));

        return [
            'documents' => $n,
            'vocabulary' => count($idf),
        ];
    }

    /** Muat index dari file. Null jika belum dibangun. */
    public function load(): ?array
    {
        if (! Storage::exists(self::INDEX_FILE)) {
            return null;
        }
        return json_decode(Storage::get(self::INDEX_FILE), true);
    }
}