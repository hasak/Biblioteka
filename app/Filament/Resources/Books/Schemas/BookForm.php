<?php

namespace App\Filament\Resources\Books\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('author')
                    ->required(),
                TextInput::make('series_id')
                    ->numeric(),
                TextInput::make('part_number')
                    ->numeric(),
                TextInput::make('publisher'),
                TextInput::make('country_id')
                    ->numeric(),
                TextInput::make('language_id')
                    ->numeric(),
                TextInput::make('genre_id')
                    ->numeric(),
                TextInput::make('original_title'),
                TextInput::make('year'),
                TextInput::make('position'),
                TextInput::make('isbn'),
                TextInput::make('user_id')
                    ->numeric(),
            ]);
    }
}
