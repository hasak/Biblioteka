<?php

namespace App\Filament\Resources\Languages\Pages;

use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\Languages\LanguageResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

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
