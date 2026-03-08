<?php

namespace App\Filament\Resources\Books\Schemas;

use App\Services\BookApi;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class BookForm
{
    static function configure(Schema $schema):Schema{
        return $schema->components([
            Grid::make(12)->schema([
                FileUpload::make('cover')
                    ->image()
                    ->imageEditor()
                    ->hiddenLabel()
                    ->directory('books/covers')
                    ->disk('public')
                    ->maxSize(2048)
                    ->imagePreviewHeight('745px')
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, callable $get){
                        $isbn=$get('isbn');
                        if(!$isbn){
                            return $file->store('books/covers', 'public');
                        }
                        $path = "books/covers/{$isbn}.jpg";
                        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));
                        return $path;
                    })
                    ->live()
                    ->columnSpan(3)
                    ->openable()
                    ->downloadable()
                    ->nullable(),
                Grid::make(1)->schema([
                    Section::make()->schema([
                        TextInput::make('title')->required(),
                        TextInput::make('author')->required(),
                        Grid::make(3)->schema([
                            Select::make('series_id')->relationship('series', 'title')->preload()->searchable()
                                ->createOptionForm([
                                    TextInput::make('title')->label("Series' Title")->required(),
                                    TextInput::make('author')->label("Series' Author")->required(),
                                    Toggle::make('is_completed')->label('Completed'),
                            ]),
                            TextInput::make('part_number')->numeric(),
                            TextInput::make('year')->numeric()->required(),
                        ]),
                        Grid::make(3)->schema([
                            Select::make('genre_id')->relationship('genre', 'name')->preload()->searchable()->required()
                                ->createOptionForm([
                                    TextInput::make('name')->label("Genre")->required(),
                            ]),
                            Select::make('language_id')->relationship('language', 'name')->preload()->searchable()->required()
                                ->createOptionForm([
                                    TextInput::make('name')->label("Language")->required(),
                                    TextInput::make('code')->label("Code")->required(),
                                ]),
                            Select::make('country_id')->relationship('country', 'name')->label('Publishing country')->preload()->searchable()->required()
                                ->createOptionForm([
                                    TextInput::make('name')->label("Country")->required(),
                                    TextInput::make('code')->label("Code")->required(),
                                ]),
                        ]),
                        Grid::make(3)->schema([
                            TextInput::make('publisher')->required(),
                            TextInput::make('original_title'),
                            TextInput::make('isbn')->label('ISBN')->live()->required()->suffixAction(
                                Action::make('fetch')
                                    ->icon(Heroicon::OutlinedBarsArrowDown)
                                    ->tooltip('Fetch book data from ISBN')
                                    ->disabled(fn (Get $get) => blank($get('isbn')))
                                    ->action(function (Get $get, Set $set){
                                        $success=0;
                                        if(self::fillFromIsbn($get, $set))
                                            $success+=1;

                                        if($cover = BookApi::fetchCover($get('isbn'))){
                                            $set('cover', $cover);
                                            $success+=2;
                                        }

                                        switch($success){
                                            case 0:
                                                Notification::make()
                                                    ->title('Error loading Book data and Cover')
                                                    ->danger()
                                                    ->send();
                                                break;
                                            case 1:
                                                Notification::make()
                                                    ->title('Book data fetched but Cover not found')
                                                    ->warning()
                                                    ->send();
                                                break;
                                            case 2:
                                                Notification::make()
                                                    ->title('Book data and Cover successfully loaded')
                                                    ->success()
                                                    ->send();
                                                break;
                                        }
                                    })
                            ),
                        ]),
                    ]),
                    Grid::make(12)->schema([
                        Section::make('Shelf position')->schema([
                            TextInput::make('shelf_x')->label('X')->numeric()->required(),
                            TextInput::make('shelf_y')->label('Y')->numeric()->required(),
                        ])->columnSpan(4),
                        Section::make('Obtained')->schema([
                            TextInput::make('purchased_city')->label('City'),
                            Select::make('purchased_country_id')->relationship('purchasedCountry', 'name')->label('Country')->preload()->searchable()
                                ->createOptionForm([
                                    TextInput::make('name')->label("Country")->required(),
                                    TextInput::make('code')->label("Code")->required(),
                                ]),
                            DatePicker::make('purchased_date')->label('Date'),
                        ])->columnSpan(4),
                        Section::make('Is read')->schema([
                            Toggle::make('is_read')->label('I read the book')->reactive()->required(),
                            DatePicker::make('read_date')->label('When')->visible(fn (Get $get) => $get('is_read')),
                        ])->columnSpan(4),
                    ])->columnSpanFull(),
                ])->columnSpan(9),
            ])->columnSpanFull(),
        ]);
    }

    private static function fillFromIsbn(Get $get, Set $set):bool{
        $isbn = $get('isbn');
        if(!$isbn)
            return false;
        $data = BookApi::fromIsbn($isbn);
        if($data){
            foreach($data as $field=>$value) {
                if($value && !$get($field)) {
                    $set($field,$value);
                }
            }
            return true;
        }else{
            return false;
        }
    }
}
