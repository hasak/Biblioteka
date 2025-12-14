<?php

namespace App\Filament\Resources\Series\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SeriesInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('author'),
                TextEntry::make('genre.name')
                    ->label('Genre'),
                //TextEntry::make('position'),
                IconEntry::make('is_completed')
                    ->boolean(),
                TextEntry::make('user.username')
                    ->label('Added by'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
