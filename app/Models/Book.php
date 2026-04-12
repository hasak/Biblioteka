<?php

namespace App\Models;

use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Book extends Model
{
    protected $fillable=[
        'cover',
        'title',
        'author',
        'series_id',
        'part_number',
        'publisher',
        'year',
        'country_id',
        'language_id',
        'original_title',
        'genre_id',
        'shelf_x',
        'shelf_y',
        'is_read',
        'read_date',
        'purchased_country_id',
        'purchased_city',
        'purchased_date',
        'isbn',
        'user_id',
    ];
    function user(){
        return $this->belongsTo(User::class);
    }
    function country(){
        return $this->belongsTo(Country::class);
    }

    function purchasedCountry(){
        return $this->belongsTo(Country::class, 'purchased_country_id');
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

    protected function purchasedIn(): Attribute{
        return Attribute::make(
            get: function () {
                if (!$this->purchasedCountry) {
                    return null;
                }

                return collect([
                    $this->purchased_city,
                    $this->purchasedCountry->withFlag(),
                ])->filter()->implode(', ');
            }
        );
    }

    protected function position(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->shelf_x}, {$this->shelf_y}",
        );
    }

    protected static function booted(): void{
        static::updating(function (Book $book) {
            if ($book->isDirty('cover')) {
                $oldCover = $book->getOriginal('cover');

                if ($oldCover) {
                    Storage::disk('public')->delete($oldCover);
                }
            }
        });

        static::deleting(function (Book $book) {
            if ($book->cover) {
                Storage::disk('public')->delete($book->cover);
            }
        });
    }
}
