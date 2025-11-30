<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
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
