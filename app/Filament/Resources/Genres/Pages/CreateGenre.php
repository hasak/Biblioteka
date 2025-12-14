<?php

namespace App\Filament\Resources\Genres\Pages;

use App\Filament\Resources\Genres\GenreResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGenre extends CreateRecord
{
    protected static string $resource = GenreResource::class;

    protected function mutateFormDataBeforeCreate(array $data):array{
        $data['user_id'] = auth()->id();
        return $data;
    }
}
