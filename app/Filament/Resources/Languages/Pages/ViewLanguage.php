<?php

namespace App\Filament\Resources\Languages\Pages;

use App\Filament\Resources\Languages\LanguageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewLanguage extends ViewRecord
{
    protected static string $resource = LanguageResource::class;

    public function getTitle():string|Htmlable{
        return $this->getRecord()->name.' ('.$this->getRecord()->code.')';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
