<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\Genres\GenreResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class GenresRelationManager extends RelationManager
{
    protected static string $relationship = 'genres';

    protected static ?string $relatedResource = GenreResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
            ])->defaultSort('name');
    }
}
