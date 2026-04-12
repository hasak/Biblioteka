<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\Books\BookResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class BooksRelationManager extends RelationManager
{
    protected static string $relationship = 'books';

    protected static ?string $relatedResource = BookResource::class;

    public function table(Table $table): Table
    {
        return BookResource::getBooksRelationTable($table);
    }
}
