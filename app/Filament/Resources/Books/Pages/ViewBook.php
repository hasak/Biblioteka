<?php

namespace App\Filament\Resources\Books\Pages;

use App\Filament\Resources\Books\BookResource;
use App\Models\Book;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewBook extends ViewRecord
{
    protected static string $resource = BookResource::class;

    public function getTitle():string|Htmlable{
        return $this->getRecord()->title;
    }

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();

        // Step through the books in the same order the list uses
        // (created_at desc), so "Next" really is the row below.
        $previous = Book::query()
            ->where(fn ($query) => $query
                ->where('created_at', '>', $record->created_at)
                ->orWhere(fn ($tie) => $tie
                    ->where('created_at', $record->created_at)
                    ->where('id', '>', $record->id)))
            ->orderBy('created_at')->orderBy('id')
            ->first();

        $next = Book::query()
            ->where(fn ($query) => $query
                ->where('created_at', '<', $record->created_at)
                ->orWhere(fn ($tie) => $tie
                    ->where('created_at', $record->created_at)
                    ->where('id', '<', $record->id)))
            ->orderByDesc('created_at')->orderByDesc('id')
            ->first();

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
