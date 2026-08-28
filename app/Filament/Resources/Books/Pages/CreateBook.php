<?php

namespace App\Filament\Resources\Books\Pages;

use App\Filament\Resources\Books\BookResource;
use App\Filament\Resources\Books\Concerns\FetchesBookCover;
use App\Services\BookApi;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateBook extends CreateRecord
{
    use FetchesBookCover;

    protected static string $resource = BookResource::class;
    public function mount(?string $isbn = null):void{
        parent::mount();
        $isbn = str_replace('-', '', (string) $isbn);
        if(!$isbn)
            return;

        $data = BookApi::fromIsbn($isbn) ?? [];
        $data['isbn'] = $isbn;
        $this->form->fill($data);

        if(count($data) > 1) {
            Notification::make()
                ->title('Book data loaded')
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Error loading Book data')
                ->danger()
                ->send();
        }

        // The cover is fetched from the browser afterwards, one source per
        // request, so the form is usable immediately and the wait can be
        // cancelled in favour of taking a photo.
        $this->dispatch('start-cover-fetch', isbn: $isbn);
    }

    protected function mutateFormDataBeforeCreate(array $data):array{
        $data['user_id'] = auth()->id();
        return $data;
    }

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction(),
            \Filament\Actions\Action::make('createAndScan')
                ->label('Create & scan another')
                ->action(function () {
                    $this->create();
                    $this->redirect(route('filament.admin.pages.scan'));
                }),
            ...($this->canCreateAnother() ? [$this->getCreateAnotherFormAction()->color('primary')] : []),
            $this->getCancelFormAction(),
        ];
    }
}
