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

        $data = BookApi::fromIsbn($isbn);
        if ($data) {
            $data['isbn'] = $isbn;
            $this->form->fill($data);

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
}
