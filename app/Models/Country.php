<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name',
        'code',
        'user_id',
    ];

    function user(){
        return $this->belongsTo(User::class);
    }

    function books(){
        return $this->hasMany(Book::class);
    }
}
