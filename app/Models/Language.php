<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable=[
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

    function withBadge(){
        return $this->name.' <span style="position: relative;top: -1px;" class="fi-text-color-600 dark:fi-text-color-200 fi-badge fi-size-sm">'.$this->code .'</span>';
    }
}
