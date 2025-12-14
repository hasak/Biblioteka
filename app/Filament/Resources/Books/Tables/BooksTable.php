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
                TextColumn::make('series.title')
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('part_number')
                    ->label('Part')
                    ->numeric()
                    ->toggleable(),
                TextColumn::make('publisher')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('country.name')
                    ->toggleable(),
                TextColumn::make('language.name')
                    ->toggleable(),
                TextColumn::make('genre.name')
                    ->toggleable(),
                TextColumn::make('original_title')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('year')
                    ->toggleable(),
                //TextColumn::make('position'),
                TextColumn::make('isbn')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('user.username')
                    ->label('Added by')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(),
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
