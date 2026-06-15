<?php

namespace App\Console\Commands;

use App\Services\TfidfService;
use Illuminate\Console\Command;

class BuildIndex extends Command
{
    /** Cara pakai: php artisan index:build */
    protected $signature = 'index:build';

    protected $description = 'Bangun index TF-IDF dari seluruh dokumen cafe dan simpan ke file';

    public function handle(TfidfService $tfidf): int
    {
        $this->info('Membangun index TF-IDF...');
        $stats = $tfidf->build();

        $this->table(
            ['Metrik', 'Jumlah'],
            [
                ['Dokumen diindeks', $stats['documents']],
                ['Ukuran kosakata (term unik)', $stats['vocabulary']],
            ]
        );
        $this->info('Index tersimpan di storage/app/tfidf_index.json');

        return self::SUCCESS;
    }
}