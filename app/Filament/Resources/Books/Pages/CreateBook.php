<?php

namespace App\Filament\Resources\Books\Pages;

use App\Services\BookApi;
use Livewire\Attributes\On;
use Filament\Notifications\Notification;
use App\Filament\Resources\Books\BookResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBook extends CreateRecord
{
    protected static string $resource = BookResource::class;

    public function mount(?string $isbn = null):void{
        parent::mount();
        if(!$isbn)
            return;
        $this->form->fill([
            'isbn' => $isbn,
        ]);
        $data=BookApi::fromIsbn($isbn);
        if($data){
            $this->form->fill($data);
            Notification::make()
                ->title('Book data loaded')
                ->success()
                ->send();
        }
        else{
            Notification::make()
                ->title('Book not found')
                ->danger()
                ->send();
        }
    }

    protected function mutateFormDataBeforeCreate(array $data):array{
        $data['user_id'] = auth()->id();
        return $data;
    }

    #[On('lwfetchcover')]
    public function lwfetchcover($isbn){
        $cover = BookApi::fetchCover($isbn);
        if (!$cover) {
            return;
        }
        $this->form->getComponent('cover')->state($cover);
            return;
        $this->form->fill([
            'cover' => $cover,
        ]);
    }
}
