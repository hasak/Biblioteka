<?php

namespace App\Filament\Resources\Books\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BookInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Basic Information')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('title'),
                            TextEntry::make('author'),
                            TextEntry::make('original_title')
                                ->label('Original title')
                                ->placeholder('—'),
                            TextEntry::make('year'),
                        ]),
                    ]),

                Section::make('Classification')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('series.title')
                                ->label('Series')
                                ->placeholder('—'),

                            TextEntry::make('part_number')
                                ->label('Part'),

                            TextEntry::make('genre.name')
                                ->label('Genre'),

                            TextEntry::make('language.name')
                                ->label('Language'),

                            TextEntry::make('country.name')
                                ->label('Country'),
                        ]),
                    ]),

                Section::make('Publishing')
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('publisher')
                                ->placeholder('—'),

                            TextEntry::make('isbn')
                                ->label('ISBN')
                                ->copyable(),
                        ]),
                    ]),

                Section::make('Meta')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)->schema([
                            TextEntry::make('user.username')
                                ->label('Added by'),

                            TextEntry::make('created_at')
                                ->dateTime(),

                            TextEntry::make('updated_at')
                                ->dateTime(),
                        ]),
                    ]),
            ]);
    }
}
