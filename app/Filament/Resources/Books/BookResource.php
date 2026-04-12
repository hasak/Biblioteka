<?php

namespace App\Filament\Resources\Books;

use Filament\Actions\CreateAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use App\Filament\Resources\Books\Pages\CreateBook;
use App\Filament\Resources\Books\Pages\EditBook;
use App\Filament\Resources\Books\Pages\ListBooks;
use App\Filament\Resources\Books\Pages\ViewBook;
use App\Filament\Resources\Books\Schemas\BookForm;
use App\Filament\Resources\Books\Schemas\BookInfolist;
use App\Filament\Resources\Books\Tables\BooksTable;
use App\Models\Book;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    public static function form(Schema $schema): Schema
    {
        return BookForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BookInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BooksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBooks::route('/'),
            'create' => CreateBook::route('/create/{isbn?}'),
            'view' => ViewBook::route('/{record}'),
            'edit' => EditBook::route('/{record}/edit'),
        ];
    }

    public static function getBooksRelationTable(Table $table): Table{
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
                    ->toggleable()
                    ->formatStateUsing(fn ($state, $record) => $record->language?->withBadge())
                    ->html(),
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
            ->recordUrl(fn ($record) => route('filament.admin.resources.books.view', $record))
            ;
    }
}
