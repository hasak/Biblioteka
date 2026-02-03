<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Genre was deliberately dropped from Series — books carry their own.
 */
class Series extends Model
{
    protected $fillable=[
        'title',
        'author',
        'is_completed',
        'user_id',
    ];

    protected function casts(): array{
        return [
            'is_completed' => 'boolean',
        ];
    }
    function user(){
        return $this->belongsTo(User::class);
    }

    function books(){
        return $this->hasMany(Book::class);
    }
}
