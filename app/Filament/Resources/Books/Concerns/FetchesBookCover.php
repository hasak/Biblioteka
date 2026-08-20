<?php

namespace App\Filament\Resources\Books\Concerns;

use App\Services\BookApi;

/**
 * Cover fetching, one source per request.
 *
 * The browser drives the loop: it calls fetchCoverStep() for each source in
 * turn and stops as soon as one returns a cover big enough. Splitting it this
 * way is what makes the progress readable and the wait cancellable — cancelling
 * is simply the browser not asking for the next source.
 */
trait FetchesBookCover
{
    /** Widest cover found so far this run, so a later source cannot replace a better one. */
    public int $coverBestWidth = 0;

    public function resetCoverFetch():void{
        $this->coverBestWidth = 0;
    }

    public function fetchCoverStep(string $isbn, string $source):array{
        $result = BookApi::fetchCoverFrom($isbn, $source);

        if(!$result){
            return ['found' => false, 'good' => false, 'width' => 0];
        }

        // Keep whichever source gave the biggest image, not simply the last one.
        if($result['width'] > $this->coverBestWidth){
            $this->coverBestWidth = $result['width'];
            $this->data['cover'] = [$result['path']];
        }

        return [
            'found' => true,
            'good' => $result['width'] >= BookApi::$minCoverWidth,
            'width' => $result['width'],
            'height' => $result['height'],
            'source' => $result['source'],
        ];
    }
}
