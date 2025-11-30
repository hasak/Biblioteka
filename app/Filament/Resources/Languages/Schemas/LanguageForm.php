<?php

namespace App\Filament\Resources\Languages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LanguageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->required(),
                TextInput::make('user_id')
                    ->numeric(),
            ]);
    }
}
