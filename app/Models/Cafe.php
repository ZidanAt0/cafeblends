<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cafe extends Model
{
    /** Kolom yang boleh diisi massal saat import. */
    protected $fillable = [
        'name',
        'city',
        'cuisine',
        'overall_rating',
        'rate_for_two',
        'review_count',
        'review_text',
        'word_count',
    ];
}
