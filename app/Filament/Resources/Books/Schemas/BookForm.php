<?php

namespace App\Filament\Resources\Books\Schemas;

use App\Services\BookApi;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class BookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('cover')
                    ->image()
                    ->imageEditor() // optional but very nice
                    ->directory('books/covers')
                    ->disk('public')
                    ->maxSize(2048) // 2 MB
                    ->imagePreviewHeight('250')
                    ->openable()
                    ->downloadable()
                    ->nullable(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('author')
                    ->required(),
                Select::make('series_id')
                    ->relationship('series', 'title'),
                TextInput::make('part_number')
                    ->numeric(),
                TextInput::make('publisher')
                    ->required(),
                TextInput::make('year')
                    ->numeric()
                    ->required(),
                Select::make('country_id')
                    ->relationship('country', 'name')
                    ->required(),
                Select::make('language_id')
                    ->relationship('language', 'name')
                    ->required(),
                TextInput::make('original_title'),
                Select::make('genre_id')
                    ->relationship('genre', 'name')
                    ->required(),
                TextInput::make('shelf_x')
                    ->label('X')
                    ->numeric()
                    ->required(),
                TextInput::make('shelf_y')
                    ->label('Y')
                    ->numeric()
                    ->required(),
                Toggle::make('is_read')
                    ->required(),
                DatePicker::make('read_date'),
                Select::make('purchased_country_id')
                    ->relationship('purchasedCountry', 'name'),
                TextInput::make('purchased_city'),
                DatePicker::make('purchased_date'),
                TextInput::make('isbn')
                    ->required()
                    ->live()
                    ->suffixAction(
                        Action::make('fetch')
                            ->icon(Heroicon::OutlinedBarsArrowDown)
                            ->tooltip('Fetch book data from ISBN')
                            ->disabled(fn (Get $get) => blank($get('isbn')))
                            ->action(function (Get $get, Set $set){
                                $isbn = $get('isbn');
                                if(!$isbn)
                                    return;
                                $data = BookApi::fromIsbn($isbn);
                                if(!$data){
                                    Notification::make()
                                        ->title('Book not found')
                                        ->danger()
                                        ->send();
                                    return;
                                }
                                foreach($data as $field=>$value) {
                                    if($value && !$get($field)) {
                                        $set($field,$value);
                                    }
                                }
                                Notification::make()
                                    ->title('Book data loaded')
                                    ->success()
                                    ->send();
                            })
                    ),
            ]);
    }
}
