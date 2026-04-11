<?php

namespace App\Filament\Resources\Users\Pages;

use Illuminate\Contracts\Support\Htmlable;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle():string|Htmlable{
        return $this->getRecord()->username;
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
