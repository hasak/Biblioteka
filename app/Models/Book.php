<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable=[
        'title',
        'author',
        'series_id',
        'part_number',
        'publisher',
        'country_id',
        'language_id',
        'genre_id',
        'original_title',
        'year',
        'isbn',
        'user_id',
    ];

    protected function casts(): array{
        return [
            'part_number' => 'float',
            'year' => 'integer',
            'is_read' => 'boolean',
            'read_date' => 'date',
            'purchased_date' => 'date',
        ];
    }

    function user(){
        return $this->belongsTo(User::class);
    }
    function country(){
        return $this->belongsTo(Country::class);
    }

    function language(){
        return $this->belongsTo(Language::class);
    }

    function genre(){
        return $this->belongsTo(Genre::class);
    }

    function series(){
        return $this->belongsTo(Series::class);
    }
}
