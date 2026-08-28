<?php

namespace App\Filament\Resources\Books\Concerns;

use App\Services\BookApi;

/**
 * Cover fetching, one source per request.
 *
 * The browser drives the loop: it calls fetchCoverStep() for every source in
 * turn and keeps the largest image of the lot. Splitting it this way is what
 * makes the progress readable and the wait cancellable — cancelling is simply
 * the browser not asking for the next source.
 */
trait FetchesBookCover
{
    /** Most pixels found so far this run, so a later source cannot replace a better one. */
    public int $coverBestPixels = 0;

    /** Where that one was stored, so it can be put back if the field is cleared. */
    public ?string $coverBestPath = null;

    public function resetCoverFetch():void{
        $this->coverBestPixels = 0;
        $this->coverBestPath = null;
    }

    /**
     * Put the winning cover back into the upload field.
     *
     * As the page settles the upload asks the server which files it holds.
     * That request goes out alongside the first fetch and carries the snapshot
     * from before the cover was stored, so its reply — arriving last — empties
     * the field again. The browser calls this once the walk is over, when
     * nothing else is in flight to undo it.
     */
    public function applyFetchedCover():void{
        if($this->coverBestPath)
            $this->data['cover'] = [$this->coverBestPath];
    }

    public function fetchCoverStep(string $isbn, string $source):array{
        $isbn = str_replace('-', '', $isbn);
        $result = BookApi::fetchCoverFrom($isbn, $source);

        if(!$result){
            return ['found' => false, 'good' => false, 'pixels' => 0];
        }

        // Only the biggest image of the run is written. Every source stores
        // under the same file name, so keeping a smaller one would replace the
        // better image already on disk.
        if($result['pixels'] > $this->coverBestPixels){
            $this->coverBestPixels = $result['pixels'];
            $this->coverBestPath = BookApi::storeCover($isbn, $result);
            $this->data['cover'] = [$this->coverBestPath];
        }

        return [
            'found' => true,
            'good' => $result['width'] >= BookApi::$minCoverWidth,
            'width' => $result['width'],
            'height' => $result['height'],
            'pixels' => $result['pixels'],
            'source' => $result['source'],
        ];
    }
}
