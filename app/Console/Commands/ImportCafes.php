<?php

namespace App\Console\Commands;

use App\Models\Cafe;
use Illuminate\Console\Command;

class ImportCafes extends Command
{
    /**
     * Cara pakai: php artisan import:cafes
     * Opsi --min-words untuk ubah ambang minimal kata (default 100, sesuai aturan tugas).
     */
    protected $signature = 'import:cafes {--min-words=100 : Minimal jumlah kata agar sebuah cafe jadi dokumen}';

    protected $description = 'Import reviews.csv: gabung review per cafe jadi 1 dokumen, lalu simpan ke DB';

    public function handle(): int
    {
        $path = base_path('reviews.csv');
        $minWords = (int) $this->option('min-words');

        if (! file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return self::FAILURE;
        }

        // 1) BACA CSV ----------------------------------------------------------
        // fgetcsv menangani koma di dalam tanda kutip dengan benar.
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);                 // baris pertama = nama kolom
        $header = array_map('trim', $header);
        $col = array_flip($header);                 // ['Name' => 1, 'Review' => 6, ...]

        // 2) GROUPING: kumpulkan semua review per nama cafe --------------------
        $groups = [];                               // ['Oliver Brown' => [...info...]]
        $rawRows = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < count($header)) {
                continue;                           // lewati baris rusak
            }
            $name   = trim($row[$col['Name']] ?? '');
            $review = trim($row[$col['Review']] ?? '');
            if ($name === '' || $review === '') {
                continue;
            }
            $rawRows++;

            if (! isset($groups[$name])) {
                $groups[$name] = [
                    'name'         => $name,
                    'city'         => trim($row[$col['City']] ?? ''),
                    'cuisine'      => trim($row[$col['Cuisine']] ?? ''),
                    'rating'       => trim($row[$col['Overall_Rating']] ?? ''),
                    'rate_for_two' => trim($row[$col['Rate for two']] ?? ''),
                    'reviews'      => [],
                ];
            }
            $groups[$name]['reviews'][] = $review;
        }
        fclose($handle);

        // 3) BANGUN DOKUMEN + FILTER >= minWords ------------------------------
        Cafe::truncate();                           // bersihkan dulu biar bisa import ulang
        $kept = 0;
        $skipped = 0;

        foreach ($groups as $g) {
            $text = implode(' ', $g['reviews']);    // gabung semua review = isi dokumen
            $wordCount = count(preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY));

            if ($wordCount < $minWords) {           // dokumen kependek -> dibuang
                $skipped++;
                continue;
            }

            Cafe::create([
                'name'           => $g['name'],
                'city'           => $g['city'] ?: null,
                'cuisine'        => $g['cuisine'] ?: null,
                'overall_rating' => is_numeric($g['rating']) ? $g['rating'] : null,
                'rate_for_two'   => $g['rate_for_two'] ?: null,
                'review_count'   => count($g['reviews']),
                'review_text'    => $text,
                'word_count'     => $wordCount,
            ]);
            $kept++;
        }

        // 4) LAPORAN -----------------------------------------------------------
        $this->info("Selesai!");
        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Baris review terbaca', $rawRows],
                ['Cafe unik (sebelum filter)', count($groups)],
                ["Dokumen tersimpan (>= {$minWords} kata)", $kept],
                ['Cafe dibuang (terlalu pendek)', $skipped],
            ]
        );

        return self::SUCCESS;
    }
}
