<?php

namespace App\Filament\Resources\Countries\Pages;

use App\Filament\Resources\Countries\CountryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewCountry extends ViewRecord
{
    protected static string $resource = CountryResource::class;

    public function getTitle():string|Htmlable{
        return $this->getRecord()->name.' '.countryCodeToFlag($this->getRecord()->code);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
