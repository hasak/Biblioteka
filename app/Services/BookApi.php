<?php
/**
 * Created by hasak on 25.01.26 @ 18:38
 **/

namespace App\Services;

use App\Models\Language;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class BookApi
{
    static int $timeout = 15;
    static function fromIsbn(string $isbn):?array{
        $google = self::fromGoogleBooks($isbn);
        $open = self::fromOpenLibrary($isbn);
        if(!$open && !$google)
            return null;

        $cover = self::getCoverFromLongitood($isbn) ?? ($open['cover'] ?? null) ?? ($google['cover'] ?? null);
        if($cover)
            self::saveCover($cover, $isbn);

        $data = $google ?? $open;
        $data['cover'] = $cover ? self::getCoverPath($isbn) : null;
        return $data;
    }

    private static function fromGoogleBooks(string $isbn):?array{
        $response=Http::timeout(self::$timeout)->get('https://www.googleapis.com/books/v1/volumes', [
            'q'=>'isbn:'.$isbn,
        ]);
        if(!$response->successful())
            return null;
        $data=$response->json('items.0.volumeInfo');
        if(!$data)
            return null;

        return [
            'cover' => $data['imageLinks']['thumbnail'] ?? $data['imageLinks']['smallThumbnail'] ?? null,
            'title' => $data['title'] ?? null,
            'author' => collect($data['authors'] ?? [])->implode(', '),
            'publisher' => $data['publisher'] ?? null,
            'year' => preg_match('/\d{4}/', $data['publishedDate'] ?? '', $m) ? (int) $m[0] : null,
            'language_id' => isset($data['language']) ? Language::where('code',substr(strtolower($data['language']),0,2))->value('id') : null,
            'isbn' => $isbn,
        ];
    }
    private static function fromOpenLibrary(string $isbn):?array{
        $response=Http::timeout(self::$timeout)->get('https://openlibrary.org/api/books', [
            'bibkeys'=>'ISBN:'.$isbn,
            'format'=>'json',
            'jscmd'=>'data',
        ]);
        if(!$response->successful())
            return null;
        $data=$response->json()['ISBN:'.$isbn]??null;
        if(!$data)
            return null;

        return [
            'cover' => $data['cover']['large'] ?? $data['cover']['medium'] ?? $data['cover']['small'] ?? null,
            'title' => $data['title'] ?? null,
            'author' => collect($data['authors'] ?? [])
                ->pluck('name')
                ->implode(', '),
            'publisher' => $data['publishers'][0]['name'] ?? null,
            'year' => preg_match('/\d{4}/', $data['publish_date'] ?? '', $m) ? (int) $m[0] : null,
            'isbn' => $isbn,
        ];
    }

    private static function getCoverFromLongitood(string $isbn):?string{
        $response=Http::timeout(self::$timeout)->get("https://bookcover.longitood.com/bookcover/".$isbn);
        if(!$response->successful())
            return null;
        return $response->json()['url'] ?? null;
    }

    private static function saveCover(string $link, string $isbn):bool{
        if(!$link || !$isbn)
            return false;
        $response=Http::timeout(self::$timeout)->get($link);
        if(!$response->successful())
            return false;
        $cover=$response->body();
        $path=self::getCoverPath($isbn);
        Storage::disk('public')->put($path, $cover);
        return true;
    }

    private static function getCoverPath(string $isbn):string{
        return "books/covers/{$isbn}.jpg";
    }
}
