<?php
/**
 * Created by hasak on 25.01.26 @ 18:38
 **/

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BookApi
{
    static function fromIsbn(string $isbn):?array{
        return self::fromGoogleBooks($isbn)??self::fromOpenLibrary($isbn);
    }
    static function fromOpenLibrary(string $isbn):?array{
        $response=Http::get('https://openlibrary.org/api/books', [
            'bibkeys'=>'ISBN:'.$isbn,
            'format'=>'json',
            'jscmd'=>'data',
        ]);
        $data=$response->json()['ISBN:'.$isbn]??null;
        if(!$data)
            return null;
        return [
            'title' => $data['title'] ?? null,
            'author' => $data['authors'][0]['name'] ?? null,
            'year' => $data['publish_date'] ?? null,
            'publisher' => $data['publishers'][0]['name'] ?? null,
            'isbn' => $isbn,
        ];
    }

    private static function fromGoogleBooks(string $isbn){
        $response = Http::get('https://www.googleapis.com/books/v1/volumes', [
            'q'=>'isbn:'.$isbn,
        ]);
        $item = $response->json('items.0.volumeInfo');
        if (!$item)
            return null;
        return [
            'title' => $item['title'] ?? null,
            'author' => $item['authors'][0] ?? null,
            'publisher' => $item['publisher'] ?? null,
            'year' => substr($item['publishedDate'] ?? '', 0, 4),
            'isbn' => $isbn,
            'language' => $item['language'] ?? null,
        ];
    }
}
