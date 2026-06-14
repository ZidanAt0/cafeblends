<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel "cafes".
     * Konsep STKI: 1 baris = 1 dokumen (gabungan semua review milik satu cafe).
     */
    public function up(): void
    {
        Schema::create('cafes', function (Blueprint $table) {
            $table->id();
            $table->string('name');                              // nama cafe = identitas dokumen
            $table->string('city')->nullable();                  // kota
            $table->text('cuisine')->nullable();                 // jenis masakan (metadata)
            $table->decimal('overall_rating', 3, 1)->nullable(); // rating rata-rata
            $table->string('rate_for_two')->nullable();          // perkiraan harga untuk 2 orang
            $table->integer('review_count')->default(0);         // jumlah review yang digabung
            $table->text('review_text');                         // gabungan semua review = isi dokumen
            $table->integer('word_count')->default(0);           // jumlah kata (untuk filter >=100)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cafes');
    }
};
