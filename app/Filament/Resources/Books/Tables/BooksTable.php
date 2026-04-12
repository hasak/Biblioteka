<?php

namespace App\Filament\Resources\Books\Tables;

use App\Models\Book;

use Illuminate\Database\Eloquent\Builder;

use App\Models\Country;

use Filament\Support\Icons\Heroicon;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
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
            // purchasedIn is an accessor, so Filament cannot infer the
            // relationship from the column name and would run one query per row.
            ->modifyQueryUsing(fn (Builder $query) => $query->with('purchasedCountry'))
            ->columns([
                ImageColumn::make('cover')
                    ->disk('public')
                    ->imageHeight(60)
                    // Only fetch the covers that are actually on screen.
                    ->extraImgAttributes(['loading' => 'lazy'])
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
                    ->width('1%')
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
                    ->toggleable()
                    ->formatStateUsing(fn ($state, $record) => $record->language?->withBadge())
                    ->html(),
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
                    ->state(fn ($record) => $record->is_read ? ($record->read_date ?? Book::UNREAD_PLACEHOLDER_DATE) : Book::UNREAD_PLACEHOLDER_DATE),
                TextColumn::make('purchasedIn')
                    // Sort through a correlated subquery: the dotted form would
                    // be rewritten into MySQL JSON-path syntax and fail.
                    ->sortable(query: fn (Builder $query, string $direction) => $query->orderBy(
                        Country::select('name')->whereColumn('countries.id', 'books.purchased_country_id'),
                        $direction,
                    ))
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
                SelectFilter::make('genre')->relationship('genre', 'name')->preload()->multiple(),
                SelectFilter::make('language')->relationship('language', 'name')->preload()->multiple(),
                SelectFilter::make('series')->relationship('series', 'title')->preload()->searchable()->multiple(),
                SelectFilter::make('shelf_y')
                    ->label('Shelf row')
                    ->options(array_combine(range(1, 6), range(1, 6))),
                TernaryFilter::make('is_read')->label('Read'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
