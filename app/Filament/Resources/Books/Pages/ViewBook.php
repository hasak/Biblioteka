<?php

namespace App\Filament\Resources\Books\Pages;

use Filament\Actions\Action;
use App\Filament\Resources\Books\BookResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBook extends ViewRecord
{
    protected static string $resource = BookResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('scan')
            ->label('Scan new')
            ->url(route('filament.admin.pages.scan')),
            EditAction::make(),
        ];
    }
}
