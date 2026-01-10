<?php

namespace App\Filament\Resources\Genres\RelationManagers;

use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\Books\BookResource;
use App\Filament\Resources\Genres\GenreResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class BooksRelationManager extends RelationManager
{
    protected static string $relationship = 'books';

    protected static ?string $relatedResource = BookResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('author')
                    ->sortable(),
                TextColumn::make('series.title')
                    ->label('Series'),
                TextColumn::make('genre.name')
                    ->label('Genre'),
                TextColumn::make('year')
                    ->sortable(),
            ])->defaultSort('title');
    }
}
