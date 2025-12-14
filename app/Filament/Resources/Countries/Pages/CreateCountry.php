<?php

namespace App\Filament\Resources\Countries\Pages;

use App\Filament\Resources\Countries\CountryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCountry extends CreateRecord
{
    protected static string $resource = CountryResource::class;

    protected function mutateFormDataBeforeCreate(array $data):array{
        $data['user_id'] = auth()->id();
        return $data;
    }
}
