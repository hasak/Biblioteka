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

    /**
     * Covers are optional, so give up on them sooner than on the book data.
     * Open Library regularly takes more than 6s, so do not tighten this
     * further — the progress panel exists so a slow source can be cancelled.
     */
    static int $coverTimeout = 9;

    /** Matches the 16 MB cap on the cover upload field. */
    static int $maxCoverBytes = 16777216;

    /**
     * Below this an image is a thumbnail rather than a cover — Google's
     * default zoom=1 is 128px wide and unreadable on the page, while Open
     * Library's usual 300-330px is small but perfectly usable. A narrower
     * image is still kept if nothing better turns up.
     */
    static int $minCoverWidth = 300;

    /** Too small to be a real cover at all; almost certainly a placeholder. */
    static int $rejectCoverWidth = 100;

    /**
     * Cover sources in the order they are tried. The form walks these one
     * request at a time so progress can be shown and the wait cancelled.
     */
    const COVER_SOURCES = [
        'google' => 'Google Books',
        'openlibrary' => 'Open Library',
        'longitood' => 'Longitood',
    ];

    static function coverSources():array{
        return collect(self::COVER_SOURCES)
            ->map(fn ($label, $key) => ['key' => $key, 'label' => $label])
            ->values()
            ->all();
    }

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
     * Try every source in turn and keep the best cover found. Used from the
     * console; the form calls fetchCoverFrom() one source at a time instead.
     */
    static function fetchCover(string $isbn):?string{
        $best = null;

        foreach(array_keys(self::COVER_SOURCES) as $source){
            $result = self::fetchCoverFrom($isbn, $source);
            if($result && $result['width'] > ($best['width'] ?? 0))
                $best = $result;
            if($best && $best['width'] >= self::$minCoverWidth)
                break;
        }

        if(!$best)
            Log::info('No cover found for ISBN', ['isbn'=>$isbn]);

        return $best['path'] ?? null;
    }

    /**
     * Fetch the best cover one single source can offer.
     *
     * Returns null when the source has nothing, otherwise the stored path
     * with the dimensions, so the caller can decide whether it is good
     * enough to stop looking.
     */
    static function fetchCoverFrom(string $isbn, string $source):?array{
        $isbn = str_replace('-', '', $isbn);
        if(!$isbn || !isset(self::COVER_SOURCES[$source]))
            return null;

        $best = null;

        foreach(self::coverUrlsFrom($isbn, $source) as $url){
            $candidate = self::downloadCover($url);
            if(!$candidate)
                continue;
            if($candidate['width'] > ($best['width'] ?? 0))
                $best = $candidate;
            // The first size that is big enough wins; no point paying for the rest.
            if($best['width'] >= self::$minCoverWidth)
                break;
        }

        if(!$best)
            return null;

        return [
            'path' => self::storeCover($isbn, $best),
            'width' => $best['width'],
            'height' => $best['height'],
            'bytes' => strlen($best['body']),
            'source' => self::COVER_SOURCES[$source],
        ];
    }

    /** Candidate URLs for one source, largest first. */
    private static function coverUrlsFrom(string $isbn, string $source):array{
        return match($source){
            'google' => self::googleCoverUrls($isbn),
            // default=false returns 404 rather than a blank placeholder.
            'openlibrary' => ["https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg?default=false"],
            'longitood' => array_filter([self::longitoodCoverUrl($isbn)]),
            default => [],
        };
    }

    /**
     * Google serves one cover at several sizes, chosen by the zoom parameter.
     * The URL it hands out uses zoom=1, which is a 128px thumbnail — useless
     * on the page — while zoom=6 on the same volume is around 1280px.
     */
    private static function googleCoverUrls(string $isbn):array{
        $links=self::googleVolumeInfo($isbn)['imageLinks'] ?? null;
        if(!$links)
            return [];

        $base=null;
        foreach(['extraLarge','large','medium','thumbnail','smallThumbnail'] as $size){
            if(!empty($links[$size])){
                $base=$links[$size];
                break;
            }
        }
        if(!$base)
            return [];

        $base=str_replace('http://', 'https://', $base);
        // The curled-page-corner overlay is baked into the image; drop it.
        $base=str_replace('&edge=curl', '', $base);

        // Only two candidates: Google throttles a burst of image requests and
        // starts 404ing them, so asking for every zoom level loses the cover
        // entirely. Big one first, the original as the fallback.
        $large=preg_match('/[?&]zoom=\d+/', $base)
            ? preg_replace('/([?&]zoom=)\d+/', '${1}6', $base)
            : $base.'&zoom=6';

        return array_values(array_unique([$large, $base]));
    }

    private static function longitoodCoverUrl(string $isbn):?string{
        // Unreachable since at least Aug 2026, so it gets a short leash.
        $response=self::get("https://bookcover.longitood.com/bookcover/{$isbn}", [], 3);
        $url=$response?->json('url');

        return is_string($url) ? $url : null;
    }

    /** Download one candidate and measure it. Null if it is not a usable image. */
    private static function downloadCover(string $url):?array{
        // The URL comes from a third party, so only follow plain https links.
        if(!filter_var($url, FILTER_VALIDATE_URL) || !str_starts_with($url, 'https://'))
            return null;
        $response=self::get($url, [], self::$coverTimeout);
        if(!$response)
            return null;
        $extension=self::coverExtension($response->header('Content-Type'));
        if(!$extension)
            return null;
        $body=$response->body();
        if($body === '' || strlen($body) > self::$maxCoverBytes)
            return null;

        $size=@getimagesizefromstring($body);
        if(!$size || $size[0] < self::$rejectCoverWidth)
            return null;

        return ['body'=>$body, 'extension'=>$extension, 'width'=>$size[0], 'height'=>$size[1]];
    }

    private static function storeCover(string $isbn, array $cover):string{
        $path="books/covers/{$isbn}.{$cover['extension']}";
        $disk=Storage::disk('public');

        // The same ISBN under a different extension would linger forever as an
        // orphan, so clear the other spellings before writing this one.
        foreach(self::COVER_EXTENSIONS as $extension){
            $stale="books/covers/{$isbn}.{$extension}";
            if($stale !== $path && $disk->exists($stale))
                $disk->delete($stale);
        }

        $disk->put($path, $cover['body']);

        return $path;
    }
}
