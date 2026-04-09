<?php

namespace App\Filament\Resources\Books\Pages;

use App\Models\Book;
use Filament\Actions\Action;
use App\Filament\Resources\Books\BookResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBook extends ViewRecord
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        $previous = Book::where('id', '<', $record->id)->orderBy('id', 'desc')->first();
        $next = Book::where('id', '>', $record->id)->orderBy('id', 'asc')->first();

        return [
            Action::make('scan')
            ->label('Scan new')
            ->url(route('filament.admin.pages.scan')),
            EditAction::make(),
            Action::make('previous')
                ->label('← Previous')
                ->url($previous ? route('filament.admin.resources.books.view', $previous) : '#')
                ->disabled(!$previous),
            Action::make('next')
                ->label('Next →')
                ->url($next ? route('filament.admin.resources.books.view', $next) : '#')
                ->disabled(!$next),
        ];
    }
}
