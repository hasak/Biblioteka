<?php
/**
 * Created by hasak on 25.01.26 @ 18:38
 **/

namespace App\Services;

use App\Models\Language;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BookApi
{
    static int $timeout = 15;

    /** Covers are optional, so give up on them sooner than on the book data. */
    static int $coverTimeout = 8;

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

    /**
     * A GET that reports failure by returning null instead of throwing. An
     * unreachable host raises ConnectionException, and one dead third party
     * must never take the whole page down with it.
     */
    private static function get(string $url, array $query = [], ?int $timeout = null):?Response{
        try{
            $response=Http::timeout($timeout ?? self::$timeout)->get($url, $query);
        }catch(\Throwable $e){
            Log::warning('BookApi request failed', ['url'=>$url, 'error'=>$e->getMessage()]);
            return null;
        }
        if(!$response->successful()){
            Log::info('BookApi request unsuccessful', ['url'=>$url, 'status'=>$response->status()]);
            return null;
        }
        return $response;
    }

    static function fromIsbn(string $isbn):?array{
        $isbn = str_replace('-', '', $isbn);
        if(!$isbn || (strlen($isbn) !== 10 && strlen($isbn) !== 13))
            return null;
        return self::fromGoogleBooks($isbn) ?? self::fromOpenLibrary($isbn) ?? null;
    }

    private static function fromGoogleBooks(string $isbn):?array{
        $data=self::googleVolumeInfo($isbn);
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

    private static function googleVolumeInfo(string $isbn):?array{
        $response=self::get('https://www.googleapis.com/books/v1/volumes', [
            'q'=>'isbn:'.$isbn,
            'key'=>config('app.google_books_api_key'),
        ]);

        return $response?->json('items.0.volumeInfo');
    }

    private static function fromOpenLibrary(string $isbn):?array{
        $response=self::get('https://openlibrary.org/api/books', [
            'bibkeys'=>'ISBN:'.$isbn,
            'format'=>'json',
            'jscmd'=>'data',
        ]);
        $data=$response?->json()['ISBN:'.$isbn]??null;
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

    /**
     * Try each cover source in turn and keep the first image that downloads.
     * Returns null when every source is dead — that is a missing cover, not
     * an error, and the rest of the book data stands without it.
     */
    static function fetchCover(string $isbn):?string{
        $isbn = str_replace('-', '', $isbn);
        if(!$isbn)
            return null;

        foreach(self::coverUrls($isbn) as $url){
            $path=self::downloadCover($isbn, $url);
            if($path)
                return $path;
        }

        Log::info('No cover found for ISBN', ['isbn'=>$isbn]);
        return null;
    }

    /**
     * Cover sources, best first. A generator so a source that needs its own
     * lookup request is only contacted once the ones before it have failed.
     */
    private static function coverUrls(string $isbn):\Generator{
        // Google Books — same response the book data already comes from.
        if($url=self::googleCoverUrl($isbn))
            yield $url;

        // Open Library — default=false returns 404 rather than a blank placeholder.
        yield "https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg?default=false";

        // The original source, kept last: it has been unreachable but may return.
        if($url=self::longitoodCoverUrl($isbn))
            yield $url;
    }

    private static function googleCoverUrl(string $isbn):?string{
        $links=self::googleVolumeInfo($isbn)['imageLinks'] ?? null;
        if(!$links)
            return null;

        // Google lists these smallest last, so take the biggest one on offer.
        foreach(['extraLarge','large','medium','thumbnail','smallThumbnail'] as $size){
            if(!empty($links[$size]))
                return str_replace('http://', 'https://', $links[$size]);
        }
        return null;
    }

    private static function longitoodCoverUrl(string $isbn):?string{
        $response=self::get("https://bookcover.longitood.com/bookcover/{$isbn}", [], self::$coverTimeout);
        $url=$response?->json('url');

        return is_string($url) ? $url : null;
    }

    private static function downloadCover(string $isbn, string $url):?string{
        // The URL comes from a third party, so only follow plain https links.
        if(!filter_var($url, FILTER_VALIDATE_URL) || !str_starts_with($url, 'https://'))
            return null;
        $response=self::get($url, [], self::$coverTimeout);
        if(!$response)
            return null;
        $extension=self::coverExtension($response->header('Content-Type'));
        if(!$extension)
            return null;
        $cover=$response->body();
        if($cover === '' || strlen($cover) > self::$maxCoverBytes)
            return null;
        $path="books/covers/{$isbn}.{$extension}";
        Storage::disk('public')->put($path, $cover);
        return $path;
    }
}
