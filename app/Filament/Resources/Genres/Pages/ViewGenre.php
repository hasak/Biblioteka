<?php

namespace App\Filament\Resources\Genres\Pages;

use App\Filament\Resources\Genres\GenreResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGenre extends ViewRecord
{
    protected static string $resource = GenreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
