<?php

namespace App\Filament\Resources\Countries\Schemas;

use Filament\Support\Enums\TextSize;
use Filament\Support\Enums\FontWeight;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CountryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Basic information')->schema([
                TextEntry::make('name')->size(TextSize::Large)->weight(FontWeight::Bold),
                TextEntry::make('code')->badge()->color('gray'),
            ]),
            Section::make('Meta')->schema([
                TextEntry::make('user.username')->label('Created by'),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
            ])->collapsed(),
        ]);
    }
}
