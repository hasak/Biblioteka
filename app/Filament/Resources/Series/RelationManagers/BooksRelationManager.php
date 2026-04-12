<?php

namespace App\Filament\Resources\Series\RelationManagers;

use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Actions\CreateAction;
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
