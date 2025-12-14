<?php

namespace App\Filament\Resources\Books\Schemas;

use Filament\Forms\Components\Select;
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
                Select::make('series.title')
                    ->relationship('series', 'title'),
                TextInput::make('part_number')
                    ->numeric(),
                TextInput::make('publisher'),
                Select::make('country_id')
                    ->relationship('country', 'name'),
                Select::make('language_id')
                    ->relationship('language', 'name'),
                Select::make('genre_id')
                    ->relationship('genre', 'name'),
                TextInput::make('original_title'),
                TextInput::make('year'),
                //TextInput::make('position'),
                TextInput::make('isbn'),
            ]);
    }
}
