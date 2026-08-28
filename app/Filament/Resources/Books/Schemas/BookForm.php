<?php

namespace App\Filament\Resources\Books\Schemas;

use App\Filament\Forms\Components\ShelfPositionInput;
use App\Services\BookApi;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
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
                    ->maxSize(16384)
                    ->imagePreviewHeight('745px')
                    ->saveUploadedFileUsing(function (TemporaryUploadedFile $file, callable $get){
                        $isbn=str_replace('-', '', (string) $get('isbn'));
                        if(!$isbn){
                            return $file->store('books/covers', 'public');
                        }
                        // Store under the file's real type, not a hardcoded .jpg.
                        $extension=BookApi::coverExtension($file->getMimeType()) ?? 'jpg';
                        return Storage::disk('public')->putFileAs('books/covers', $file, "{$isbn}.{$extension}");
                    })
                    ->live()
                    ->columnSpan(3)
                    ->openable()
                    ->downloadable()
                    ->nullable()
                    // Progress for the cover fetch, with a cancel button, so a
                    // slow source can be abandoned in favour of a photo.
                    ->belowContent(View::make('filament.forms.cover-status')),
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
                            TextInput::make('part_number')->label('Part')->numeric()->extraInputAttributes([
                                'type' => 'text',
                                'inputmode' => 'decimal',
                                'oninput' => "this.value = this.value.replace(',', '.')"
                            ]),
                            TextInput::make('original_title'),
                        ]),
                        Grid::make(3)->schema([
                            Select::make('genre_id')->relationship('genre', 'name')->preload()->required()
                                ->createOptionForm([
                                    TextInput::make('name')->label("Genre")->required()->unique('genres', 'name'),
                            ]),
                            Select::make('language_id')->relationship('language', 'name')->preload()->required()
                                ->createOptionForm([
                                    TextInput::make('name')->label("Language")->required()->unique('languages', 'name'),
                                    TextInput::make('code')->label("Code")->required()->unique('languages', 'code'),
                                ]),
                            TextInput::make('year')->numeric()->required(),
                        ]),
                        Grid::make(3)->schema([
                            TextInput::make('publisher')->required(),
                            Select::make('country_id')->relationship('country', 'name')->label('Publishing country')->preload()->required()
                                ->createOptionForm([
                                    TextInput::make('name')->label("Country")->required()->unique('countries', 'name'),
                                    TextInput::make('code')->label("Code")->required()->unique('countries', 'code'),
                                ]),
                            // Debounced: without it every keystroke is its own
                            // round trip, and a slow one landing late can undo
                            // what has been typed since.
                            TextInput::make('isbn')->label('ISBN')->live(debounce: '500ms')->required()
                                ->unique(ignoreRecord: true)
                                // Strip dashes as they are typed so the validated
                                // value matches the dash-free value that gets stored.
                                ->extraInputAttributes([
                                    'oninput' => "this.value = this.value.replace(/-/g, '')",
                                ])
                                ->suffixAction(
                                Action::make('fetch')
                                    ->icon(Heroicon::OutlinedBarsArrowDown)
                                    ->tooltip('Fetch book data from ISBN')
                                    ->disabled(fn (Get $get) => blank($get('isbn')))
                                    ->action(function (Get $get, Set $set, $livewire){
                                        self::fillFromIsbn($get, $set);
                                        // The browser takes it from here, one source at a time.
                                        $livewire->dispatch('start-cover-fetch', isbn: str_replace('-', '', (string) $get('isbn')));
                                    })
                            )->dehydrateStateUsing(fn ($state) => str_replace('-', '', $state)),
                        ]),
                    ]),
                    Grid::make(12)->schema([
                        Section::make('Shelf position')->schema([
                            ShelfPositionInput::make('position')->hiddenLabel(),
                            Grid::make(['default' => 2])->schema([
                                TextInput::make('shelf_x')->label('X')->numeric()->minValue(1)->maxValue(6)->required(),
                                TextInput::make('shelf_y')->label('Y')->numeric()->minValue(1)->maxValue(6)->required(),
                            ]),
                        ])->columnSpan(4),
                        Section::make('Obtained')->schema([
                            TextInput::make('purchased_city')->label('City'),
                            Select::make('purchased_country_id')->relationship('purchasedCountry', 'name')->label('Country')->preload()
                                ->createOptionForm([
                                    TextInput::make('name')->label("Country")->required()->unique('countries', 'name'),
                                    TextInput::make('code')->label("Code")->required()->unique('countries', 'code'),
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

    static function fillFromIsbn(Get $get, Set $set):bool{
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
            Notification::make()
                ->title('Book data loaded')
                ->success()
                ->send();
            return true;
        }else{
            Notification::make()
                ->title('Error loading Book data')
                ->danger()
                ->send();
            return false;
        }
    }
}
