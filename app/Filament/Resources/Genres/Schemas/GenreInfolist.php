<?php

namespace App\Filament\Resources\Genres\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class GenreInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name'),
                TextEntry::make('user.username')
                    ->label('Added by'),
                TextEntry::make('created_at')
                    ->dateTime(),
                TextEntry::make('updated_at')
                    ->dateTime(),
            ]);
    }
}
