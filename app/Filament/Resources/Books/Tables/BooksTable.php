<?php

namespace App\Filament\Resources\Books\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('author')
                    ->searchable(),
                TextColumn::make('series_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('part_number')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('publisher')
                    ->searchable(),
                TextColumn::make('country_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('language_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('genre_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('original_title')
                    ->searchable(),
                TextColumn::make('year'),
                TextColumn::make('position'),
                TextColumn::make('isbn')
                    ->searchable(),
                TextColumn::make('user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
