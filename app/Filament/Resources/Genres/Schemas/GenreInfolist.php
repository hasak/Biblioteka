<?php

namespace App\Filament\Resources\Genres\Schemas;

use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\FontWeight;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GenreInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Grid::make(12)->schema([
                Section::make('Basic information')->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('name')->extraAttributes(['style' => 'font-size: 1.5rem;']),
                    ]),
                ])->columnSpan(9),
                Section::make('Meta')->schema([
                    TextEntry::make('user.username')->label('Created by'),
                    TextEntry::make('created_at')->dateTime(),
                    TextEntry::make('updated_at')->dateTime(),
                ])->columnSpan(3)->collapsed()
            ])->columnSpanFull()
        ]);
    }
}
