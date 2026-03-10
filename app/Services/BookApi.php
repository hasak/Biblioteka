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

    /** Matches the 16 MB cap on the cover upload field. */
    static int $maxCoverBytes = 16777216;

    /** Image types we are willing to store, and the extension each gets on disk. */
    const COVER_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
        'image/gif' => 'gif',
    ];

    /**
     * Map a mime type to the extension a cover should be stored under.
     * Returns null for anything that is not an image we accept.
     */
    static function coverExtension(?string $mime):?string{
        $mime = strtolower(trim(explode(';', (string) $mime)[0]));

        return self::COVER_EXTENSIONS[$mime] ?? null;
    }
    static function fromIsbn(string $isbn):?array{
        $isbn = str_replace('-', '', $isbn);
        if(!$isbn || (strlen($isbn) !== 10 && strlen($isbn) !== 13))
            return null;
        return self::fromGoogleBooks($isbn) ?? self::fromOpenLibrary($isbn) ?? null;
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
            'title' => $data['title'] ?? null,
            'author' => collect($data['authors'] ?? [])
                ->pluck('name')
                ->implode(', '),
            'publisher' => $data['publishers'][0]['name'] ?? null,
            'year' => preg_match('/\d{4}/', $data['publish_date'] ?? '', $m) ? (int) $m[0] : null,
            'isbn' => $isbn,
        ];
    }

    static function fetchCover(string $isbn):?string{
        if(!$isbn)
            return null;
        $response=Http::timeout(self::$timeout)->get("https://bookcover.longitood.com/bookcover/".$isbn);
        if(!$response->successful())
            return null;
        $url = $response->json('url');
        // The URL comes from a third party, so only follow plain https links.
        if(!$url || !filter_var($url, FILTER_VALIDATE_URL) || !str_starts_with($url, 'https://'))
            return null;
        $response=Http::timeout(self::$timeout)->get($url);
        if(!$response->successful())
            return null;
        $extension=self::coverExtension($response->header('Content-Type'));
        if(!$extension)
            return null;
        $cover=$response->body();
        if(strlen($cover) > self::$maxCoverBytes)
            return null;
        $path="books/covers/{$isbn}.{$extension}";
        Storage::disk('public')->put($path, $cover);
        return $path;
    }
}
