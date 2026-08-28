<?php

namespace App\Filament\Resources\Books\Tables;

use App\Filament\Forms\Components\SelectedBooksList;
use App\Filament\Forms\Components\ShelfPositionInput;
use App\Models\Book;

use App\Models\Country;

use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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
                    self::moveToShelfAction(),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    /**
     * Move a batch of books to one slot on the shelf.
     *
     * The modal puts the shelf grid next to the books it is about to move, and
     * a book can be dropped from that list first — nothing is written until
     * the modal is confirmed.
     */
    private static function moveToShelfAction(): BulkAction
    {
        return BulkAction::make('moveToShelf')
            ->label('Move to shelf')
            ->icon(Heroicon::OutlinedSquares2x2)
            ->modalWidth(Width::FourExtraLarge)
            ->modalSubmitActionLabel('Move')
            ->fillForm(fn (Collection $records): array => [
                'book_ids' => $records->pluck('id')->all(),
                // A selection that already sits on one slot starts there.
                'shelf_x' => $records->pluck('shelf_x')->unique()->count() === 1 ? $records->first()->shelf_x : null,
                'shelf_y' => $records->pluck('shelf_y')->unique()->count() === 1 ? $records->first()->shelf_y : null,
            ])
            ->schema(fn (Collection $records): array => [
                Grid::make(3)->schema([
                    Section::make('New position')->schema([
                        ShelfPositionInput::make('position')->hiddenLabel(),
                        Grid::make(2)->schema([
                            TextInput::make('shelf_x')->label('X')->numeric()->minValue(1)->maxValue(6)->required(),
                            TextInput::make('shelf_y')->label('Y')->numeric()->minValue(1)->maxValue(6)->required(),
                        ]),
                    ])->columnSpan(2),
                    Section::make('Books')->schema([
                        SelectedBooksList::make('book_ids')
                            ->label('Books')
                            ->hiddenLabel()
                            ->books($records->map(fn (Book $book): array => [
                                'id' => $book->id,
                                'title' => $book->title,
                                'author' => $book->author,
                                'position' => $book->position,
                            ])->all())
                            ->rules(['array', 'min:1']),
                    ])->columnSpan(1),
                ]),
            ])
            ->action(function (array $data): void {
                // Straight to the database: the model's only hooks are about
                // covers, and one statement beats loading every book.
                $moved = Book::withTrashed()
                    ->whereIn('id', $data['book_ids'])
                    ->update(['shelf_x' => $data['shelf_x'], 'shelf_y' => $data['shelf_y']]);

                Notification::make()
                    ->title($moved . ' ' . str('book')->plural($moved) . " moved to shelf ({$data['shelf_x']},{$data['shelf_y']})")
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }
}
