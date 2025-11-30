<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    function user(){
        return $this->belongsTo(User::class);
    }

    function genre(){
        return $this->belongsTo(Genre::class);
    }

    function books(){
        return $this->hasMany(Book::class);
    }
}
