<?php

namespace App\Filament\Resources\Books\Pages;

use App\Services\BookApi;
use Livewire\Attributes\On;
use Filament\Notifications\Notification;
use App\Filament\Resources\Books\BookResource;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Books\Schemas\BookForm;

class CreateBook extends CreateRecord
{
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

        // Fetch the cover in a follow-up request instead of here, so the form
        // is usable straight away while slow cover sources are still being tried.
        $this->dispatch('lwfetchcover', isbn: $isbn);
    }

    protected function mutateFormDataBeforeCreate(array $data):array{
        $data['user_id'] = auth()->id();
        return $data;
    }

    #[On('lwfetchcover')]
    public function lwfetchcover($isbn){
        $cover = BookApi::fetchCover($isbn);

        // A missing cover is not an error: everything else about the book is
        // already filled in, and the user can still upload one by hand.
        if (!$cover) {
            Notification::make()
                ->title('No cover found')
                ->body('Add one by hand if you have it.')
                ->warning()
                ->send();
            return;
        }

        $this->data['cover'] = [$cover];

        Notification::make()
            ->title('Cover loaded')
            ->success()
            ->send();
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
