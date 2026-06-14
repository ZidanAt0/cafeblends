<?php

namespace App\Services;

/**
 * TextPreprocessor
 * --------------------------------------------------------------------------
 * Mengubah teks mentah (review cafe) menjadi daftar token (kata dasar) yang
 * bersih dan siap dihitung TF-IDF. Dataset berbahasa Inggris, jadi stopword
 * dan stemming-nya versi Inggris.
 *
 * Alur 5 tahap:
 *   1. Case folding   -> semua huruf kecil
 *   2. Cleaning       -> buang angka, tanda baca, emoji (sisakan huruf saja)
 *   3. Tokenization   -> pecah kalimat jadi array kata
 *   4. Stopword removal-> buang kata umum tak bermakna (the, a, is, ...)
 *   5. Stemming       -> ubah ke bentuk dasar (running -> run, places -> place)
 */
class TextPreprocessor
{
    /**
     * Daftar stopword Bahasa Inggris (kata yang terlalu umum, tidak membedakan
     * satu dokumen dengan dokumen lain, jadi dibuang).
     */
    private array $stopwords = [
        'a','an','the','and','or','but','if','of','at','by','for','with','about',
        'to','from','in','on','off','out','over','under','again','further','then',
        'once','here','there','all','any','both','each','few','more','most','other',
        'some','such','no','nor','not','only','own','same','so','than','too','very',
        'can','will','just','should','now','is','am','are','was','were','be','been',
        'being','have','has','had','having','do','does','did','doing','this','that',
        'these','those','i','me','my','we','our','you','your','he','she','it','its',
        'they','them','their','what','which','who','whom','when','where','why','how',
        'as','until','while','because','also','was','were','get','got','would','could',
    ];

    /**
     * Jalankan seluruh pipeline preprocessing.
     *
     * @return string[] daftar token (kata dasar) yang sudah bersih
     */
    public function process(string $text): array
    {
        $text   = $this->caseFolding($text);   // 1
        $text   = $this->cleaning($text);      // 2
        $tokens = $this->tokenize($text);      // 3
        $tokens = $this->removeStopwords($tokens); // 4
        $tokens = array_map([$this, 'stem'], $tokens); // 5

        // Buang token kosong / terlalu pendek sisa proses
        return array_values(array_filter($tokens, fn ($t) => strlen($t) > 2));
    }

    /** 1. Ubah semua huruf jadi kecil: "Coffee" dan "coffee" dianggap sama. */
    private function caseFolding(string $text): string
    {
        return mb_strtolower($text);
    }

    /** 2. Sisakan huruf a-z dan spasi; angka/tanda baca/emoji jadi spasi. */
    private function cleaning(string $text): string
    {
        $text = preg_replace('/[^a-z\s]/', ' ', $text);
        return preg_replace('/\s+/', ' ', trim($text)); // rapikan spasi ganda
    }

    /** 3. Pecah string jadi array kata berdasarkan spasi. */
    private function tokenize(string $text): array
    {
        if ($text === '') {
            return [];
        }
        return explode(' ', $text);
    }

    /** 4. Buang token yang termasuk stopword. */
    private function removeStopwords(array $tokens): array
    {
        $stop = array_flip($this->stopwords); // flip -> pencarian O(1)
        return array_filter($tokens, fn ($t) => ! isset($stop[$t]));
    }

    /**
     * 5. Stemming sederhana (suffix stripping) untuk Bahasa Inggris.
     * Tujuannya menyamakan kata yang seakar: "tasty/tastes", "served/serving"
     * -> mendekati akar yang sama, sehingga dihitung sebagai term yang sama.
     * Versi ringan & mudah dijelaskan (bukan Porter penuh, tapi cukup efektif).
     */
    private function stem(string $word): string
    {
        // Aturan jamak / kata kerja
        $rules = [
            '/ies$/'  => 'y',    // studies -> study
            '/sses$/' => 'ss',   // classes -> class
            '/ied$/'  => 'y',    // tried -> try
            '/ying$/' => 'y',    // trying -> try
            '/ing$/'  => '',     // serving -> serv
            '/edly$/' => '',
            '/ed$/'   => '',     // served -> serv
            '/ly$/'   => '',     // quickly -> quick
            '/es$/'   => '',     // dishes -> dish
            '/s$/'    => '',     // drinks -> drink
        ];

        foreach ($rules as $pattern => $replace) {
            if (preg_match($pattern, $word)) {
                $stemmed = preg_replace($pattern, $replace, $word);
                // jangan stem kalau hasilnya jadi terlalu pendek (mis. < 3 huruf)
                if (strlen($stemmed) >= 3) {
                    return $stemmed;
                }
            }
        }

        return $word;
    }
}

