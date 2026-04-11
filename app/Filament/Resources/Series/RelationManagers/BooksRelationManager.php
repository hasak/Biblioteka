<?php

namespace App\Filament\Resources\Series\RelationManagers;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use App\Filament\Resources\Series\SeriesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class BooksRelationManager extends RelationManager
{
    protected static string $relationship = 'books';

    protected static ?string $relatedResource = SeriesResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ])
            ->columns([
                TextColumn::make('part_number')
                    ->label('Part')
                    ->width('1%')
                    ->numeric()
                    ->sortable(),
                ImageColumn::make('cover')
                    ->disk('public')
                    ->imageHeight(60)
                    ->toggleable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('publisher')
                    ->toggleable(),
                TextColumn::make('language.name')
                    ->toggleable(),
                TextColumn::make('genre.name')
                    ->toggleable(),
                TextColumn::make('position')
                    ->toggleable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('is_read')
                    ->date(fn ($record) => $record->is_read ? ($record->read_date ? 'j. n. Y.' : '') : '')
                    ->sortable(['read_date'])
                    ->toggleable()
                    ->icon(fn ($record) => $record->is_read ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedXCircle)
                    ->iconColor(fn ($record) => $record->is_read ? 'success' : 'danger')
                    ->state(fn ($record) => $record->is_read ? ($record->read_date ?? '1999-06-30') : '1999-06-30'),
                TextColumn::make('isbn')
                    ->label('ISBN')
                    ->searchable(),
            ])
            ->recordActions([])
            ->recordUrl(fn ($record) => route('filament.admin.resources.books.view', $record));
    }
}
