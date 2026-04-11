<?php

namespace App\Filament\Resources\Series\Pages;

use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\Series\SeriesResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewSeries extends ViewRecord
{
    protected static string $resource = SeriesResource::class;

    public function getTitle():string|Htmlable{
        return $this->getRecord()->title;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
