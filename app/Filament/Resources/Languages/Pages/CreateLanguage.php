<?php

namespace App\Filament\Resources\Languages\Pages;

use App\Filament\Resources\Languages\LanguageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLanguage extends CreateRecord
{
    protected static string $resource = LanguageResource::class;

    protected function mutateFormDataBeforeCreate(array $data):array{
        $data['user_id'] = auth()->id();
        return $data;
    }
}
