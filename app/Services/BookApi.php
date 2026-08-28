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

    /** Goodreads answers 403 to a request that does not name a browser. */
    const USER_AGENT = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36';

    /**
     * Cover sources in the order they are tried. The form walks these one
     * request at a time so progress can be shown and the wait cancelled.
     */
    const COVER_SOURCES = [
        'goodreads' => 'Goodreads',
        'google' => 'Google Books',
        'openlibrary' => 'Open Library',
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
    private static function get(string $url, array $query = [], ?int $timeout = null, array $headers = []):?Response{
        try{
            $response=Http::withHeaders($headers)->timeout($timeout ?? self::$timeout)->get($url, $query);
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
     * Ask every source and keep the largest cover of them all. Used from the
     * console; the form calls fetchCoverFrom() one source at a time instead.
     */
    static function fetchCover(string $isbn):?string{
        $isbn = str_replace('-', '', $isbn);
        $best = null;

        foreach(array_keys(self::COVER_SOURCES) as $source){
            $result = self::fetchCoverFrom($isbn, $source);
            if(self::pixels($result) > self::pixels($best))
                $best = $result;
        }

        if(!$best){
            Log::info('No cover found for ISBN', ['isbn'=>$isbn]);
            return null;
        }

        return self::storeCover($isbn, $best);
    }

    /** How much cover there is to look at. Nothing at all counts as zero. */
    static function pixels(?array $cover):int{
        return $cover ? $cover['width'] * $cover['height'] : 0;
    }

    /**
     * Fetch the best cover one single source can offer.
     *
     * The image itself is handed back rather than written to disk: all three
     * sources store under the same file name, so only the winner of the whole
     * run may be stored, and the caller is the one who knows the winner.
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
            if(self::pixels($candidate) > self::pixels($best))
                $best = $candidate;
            // One source's candidates come largest first, so the first size
            // that is big enough ends it; no point paying for the rest.
            if($best['width'] >= self::$minCoverWidth)
                break;
        }

        if(!$best)
            return null;

        return $best + [
            'pixels' => self::pixels($best),
            'bytes' => strlen($best['body']),
            'source' => self::COVER_SOURCES[$source],
        ];
    }

    /** Candidate URLs for one source, largest first. */
    private static function coverUrlsFrom(string $isbn, string $source):array{
        return match($source){
            'goodreads' => self::goodreadsCoverUrls($isbn),
            'google' => self::googleCoverUrls($isbn),
            // default=false returns 404 rather than a blank placeholder.
            'openlibrary' => ["https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg?default=false"],
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

    /**
     * Goodreads has no public API, so the cover is read off the page the way
     * bookcover-api (github.com/w3slley/bookcover-api) does it: a search for
     * the ISBN redirects to that book, and the page's og:image is the cover.
     * Scraped markup can change overnight, which is why the other sources are
     * still there behind it.
     */
    private static function goodreadsCoverUrls(string $isbn):array{
        // The search guesses: a number that is not a real ISBN still lands on
        // some unrelated book, whose cover would then be stored as this one's.
        if(!self::isRealIsbn($isbn))
            return [];

        $response=self::get('https://www.goodreads.com/search', ['q'=>$isbn], self::$coverTimeout, [
            'User-Agent'=>self::USER_AGENT,
        ]);
        if(!$response || !preg_match('/<meta property="og:image" content="([^"]+)"/', $response->body(), $match))
            return [];

        $url=html_entity_decode($match[1]);
        // A book with no cover shows the Goodreads logo or a nophoto
        // placeholder, both served from /assets/ rather than /books/. So is
        // the search page itself, when the ISBN matched nothing at all.
        if(!str_contains($url, '/books/'))
            return [];

        // A resized variant carries a token like ._SY475_ in the file name;
        // without it Goodreads serves the original upload, the largest it has.
        $original=preg_replace('/\._[^.\/]*_\./', '.', $url);

        return array_values(array_unique([$original, $url]));
    }

    /**
     * The EAN-13 and ISBN-10 checksums, the same ones the scan page validates
     * with. Books entered without a real ISBN carry a sequential placeholder,
     * and those must never be handed to a search that guesses.
     */
    private static function isRealIsbn(string $isbn):bool{
        if(strlen($isbn) === 13){
            if(!ctype_digit($isbn) || (!str_starts_with($isbn, '978') && !str_starts_with($isbn, '979')))
                return false;

            $sum=0;
            foreach(str_split($isbn) as $i=>$digit)
                $sum+=($i % 2 === 0) ? (int) $digit : 3 * (int) $digit;

            return $sum % 10 === 0;
        }

        if(strlen($isbn) === 10){
            if(!ctype_digit(substr($isbn, 0, 9)))
                return false;

            $last=strtoupper($isbn[9]);
            if($last !== 'X' && !ctype_digit($last))
                return false;

            $sum=0;
            foreach(str_split(substr($isbn, 0, 9)) as $i=>$digit)
                $sum+=(10 - $i) * (int) $digit;
            $sum+=($last === 'X') ? 10 : (int) $last;

            return $sum % 11 === 0;
        }

        return false;
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

    /** Write one cover to disk, replacing whatever this ISBN had before. */
    static function storeCover(string $isbn, array $cover):string{
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
