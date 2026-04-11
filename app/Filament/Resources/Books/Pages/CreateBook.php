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
    public ?string $isbnForCover = null;
    public function mount(?string $isbn = null):void{
        parent::mount();
        $isbn = str_replace('-', '', $isbn);
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

        $this->isbnForCover = $isbn;
    }

    protected function mutateFormDataBeforeCreate(array $data):array{
        $data['user_id'] = auth()->id();
        return $data;
    }

    public function hydrate(): void
    {
        if ($this->isbnForCover) {
            $cover = BookApi::fetchCover($this->isbnForCover);

            if ($cover) {
                $this->data['cover'] = [$cover];
            }

            $this->isbnForCover = null;
        }
    }

    #[On('lwfetchcover')]
    public function lwfetchcover($isbn){
        $cover = BookApi::fetchCover($isbn);

        if (!$cover) {
            Notification::make()
                ->title('Error fetching cover')
                ->danger()
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
