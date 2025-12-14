<?php

namespace App\Filament\Resources\Series\Pages;

use App\Filament\Resources\Series\SeriesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSeries extends CreateRecord
{
    protected static string $resource = SeriesResource::class;

    protected function mutateFormDataBeforeCreate(array $data):array{
        $data['user_id'] = auth()->id();
        return $data;
    }
}
