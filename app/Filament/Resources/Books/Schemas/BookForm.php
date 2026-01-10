<?php

namespace App\Filament\Resources\Books\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('cover')
                    ->image()
                    ->imageEditor() // optional but very nice
                    ->directory('books/covers')
                    ->disk('public')
                    ->maxSize(2048) // 2 MB
                    ->imagePreviewHeight('250')
                    ->openable()
                    ->downloadable()
                    ->nullable(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('author')
                    ->required(),
                Select::make('series_id')
                    ->relationship('series', 'title'),
                TextInput::make('part_number')
                    ->numeric(),
                TextInput::make('publisher')
                    ->required(),
                TextInput::make('year')
                    ->numeric()
                    ->required(),
                Select::make('country_id')
                    ->relationship('country', 'name')
                    ->required(),
                Select::make('language_id')
                    ->relationship('language', 'name')
                    ->required(),
                TextInput::make('original_title'),
                Select::make('genre_id')
                    ->relationship('genre', 'name')
                    ->required(),
                TextInput::make('shelf_x')
                    ->label('X')
                    ->numeric()
                    ->required(),
                TextInput::make('shelf_y')
                    ->label('Y')
                    ->numeric()
                    ->required(),
                Toggle::make('is_read')
                    ->required(),
                DatePicker::make('read_date'),
                Select::make('purchased_country_id')
                    ->relationship('purchasedCountry', 'name'),
                TextInput::make('purchased_city'),
                DatePicker::make('purchased_date'),
                TextInput::make('isbn')
                    ->required(),
            ]);
    }
}
