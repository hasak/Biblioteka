<?php

namespace App\Filament\Resources\Books\Tables;

use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\ImageColumn;

class BooksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('cover')
                    ->disk('public')
                    ->imageHeight(60)
                    ->toggleable(),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('author')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('series.title')
                    ->numeric()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('part_number')
                    ->label('Part')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('publisher')
                    ->toggleable(),
                TextColumn::make('year')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('country.name')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('language.name')
                    ->toggleable(),
                TextColumn::make('original_title')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                TextColumn::make('purchasedIn')
                    ->sortable(['purchasedCountry.name'])
                    ->toggleable(),
                TextColumn::make('purchased_date')
                    ->label('Date of purchase')
                    ->date('j. n. Y.')
                    ->toggleable(),
                TextColumn::make('isbn')
                    ->label('ISBN')
                    ->searchable(),
                TextColumn::make('user.username')
                    ->label('Added by')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime('j. n. Y. @ G:i')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime('j. n. Y. @ G:i')
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
            ])
            ->defaultSort('created_at', 'desc');
    }
}
