<?php

namespace App\Filament\Resources\Genres\Pages;

use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\Genres\GenreResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGenre extends ViewRecord
{
    protected static string $resource = GenreResource::class;

    public function getTitle():string|Htmlable{
        return $this->getRecord()->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
