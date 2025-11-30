<?php

namespace App\Filament\Resources\Books\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BookInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('author'),
                TextEntry::make('series_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('part_number')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('publisher')
                    ->placeholder('-'),
                TextEntry::make('country_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('language_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('genre_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('original_title')
                    ->placeholder('-'),
                TextEntry::make('year')
                    ->placeholder('-'),
                TextEntry::make('position')
                    ->placeholder('-'),
                TextEntry::make('isbn')
                    ->placeholder('-'),
                TextEntry::make('user_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
