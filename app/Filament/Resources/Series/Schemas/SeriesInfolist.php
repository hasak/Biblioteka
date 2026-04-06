<?php

namespace App\Filament\Resources\Series\Schemas;

use Filament\Support\Enums\TextSize;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\FontWeight;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SeriesInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Basic information')->schema([
                Grid::make(2)->schema([
                    TextEntry::make('title')->extraAttributes(['style' => 'font-size: 1.5rem;']),
                    TextEntry::make('author'),
                    IconEntry::make('is_completed')->boolean(),
                ]),
            ]),
            Section::make('Meta')->schema([
                TextEntry::make('user.username')->label('Created by'),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
            ])->collapsed(),
        ]);
    }
}
