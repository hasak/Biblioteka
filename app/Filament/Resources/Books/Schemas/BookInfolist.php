<?php

namespace App\Filament\Resources\Books\Schemas;

use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\FontWeight;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\ImageEntry;
use App\Filament\Infolists\Components\ShelfPositionEntry;

class BookInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(12)->schema([
                ImageEntry::make('cover')
                    ->disk('public')
                    ->hiddenLabel()
                    ->imageHeight('auto')
                    ->imageWidth('100%')
                    ->columnSpan(3)
                    ->defaultImageUrl(url('storage/books/covers/default.png'))
                    ->extraImgAttributes(['style' => "border-radius: 0.75rem;"]),
                Grid::make(1)->schema([
                    Section::make()->schema([
                        TextEntry::make('title')->hiddenLabel()->extraAttributes(['style' => 'font-size: 2.5rem;']),
                        TextEntry::make('author'),
                        Grid::make(3)->schema([
                            TextEntry::make('series.title')->label('Series')->visible(fn ($record) => $record->series != null),
                            TextEntry::make('part_number')->label('Part')->visible(fn ($record) => $record->part_number != null || $record->series != null),
                            TextEntry::make('original_title')->visible(fn ($record) => $record->original_title != null),
                        ])->visible(fn ($record) => $record->series != null || $record->part_number != null || $record->original_title != null),
                        Grid::make(3)->schema([
                            TextEntry::make('genre.name')->label('Genre'),
                            TextEntry::make('language.name')
                                ->label('Language')
                                ->formatStateUsing(fn ($state, $record) => $state . ' <span style="position: relative;top: -1px;" class="fi-text-color-600 dark:fi-text-color-200 fi-badge fi-size-sm">' . $record->language?->code . '</span>')
                                ->html(),
                            TextEntry::make('year'),
                        ]),
                        Grid::make(3)->schema([
                            TextEntry::make('publisher'),
                            TextEntry::make('country.name')
                                ->label('Publishing country')
                                ->formatStateUsing(fn ($state, $record) => $state . ' ' . countryCodeToFlag($record->country?->code))
                                ->html(),
                            TextEntry::make('isbn')->label('ISBN')->copyable(),
                        ]),
                    ]),
                    Grid::make(12)->schema([
                        Section::make(fn($record) => 'Shelf position ('.$record->shelf_x.','.$record->shelf_y.')')->schema([
                            ShelfPositionEntry::make('shelf_position')->hiddenLabel(),
                        ])->columnSpan(3),
                        Section::make('Obtained')->schema([
                            TextEntry::make('purchased_in')
                                ->hiddenLabel()
                                ->state(function ($record) {
                                    $ret = "";
                                    if($record->purchased_city){
                                        $ret .= $record->purchased_city;
                                        if($record->purchased_city === 'Tesanj' || $record->purchased_city === 'Tešanj'){
                                            $ret .= ' <img src="'.asset('images/tesanj.png').'" style="height:1em;vertical-align:center;display:inline" />';
                                        }
                                        $ret .=', ';
                                    }
                                    $ret .= $record->purchasedCountry->name;
                                    $ret .= ' ';
                                    $ret .= countryCodeToFlag($record->purchasedCountry?->code);
                                    return $ret;
                                })
                                ->html()
                                ->visible(fn ($record) => $record->purchasedCountry != null),
                            TextEntry::make('purchased_date')
                                ->hiddenLabel()
                                ->date('j. n. Y.'),
                        ])->columnSpan(3),
                        //Grid::make(1)->schema([
                            Section::make('Is read')->schema([
                                TextEntry::make('is_read')
                                    ->date(fn ($record) => $record->is_read ? ($record->read_date ? 'j. n. Y.' : '') : '')
                                    ->icon(fn ($record) => $record->is_read ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedXCircle)
                                    ->iconColor(fn ($record) => $record->is_read ? 'success' : 'danger')
                                    ->state(fn ($record) => $record->is_read ? ($record->read_date ?? '1999-06-30') : '1999-06-30')
                                    ->hiddenLabel(),
                            ])->columnSpan(3),
                            Section::make('Meta')->schema([
                                TextEntry::make('user.username')->label('Created by'),
                                TextEntry::make('created_at')->dateTime(),
                                TextEntry::make('updated_at')->dateTime(),
                            ])->columnSpan(3)->collapsed(),
                        //])->columnSpan(4),
                    ])->columnSpanFull(),
                ])->columnSpan(9),
            ])->columnSpanFull(),
        ]);
        /*
        return $schema->schema([
            Section::make('Basic information')->schema([
                TextEntry::make('name')->size(TextSize::Large)->weight(FontWeight::Bold),
                TextEntry::make('code')->badge()->color('gray'),
            ]),
            Section::make('Meta')->schema([
                TextEntry::make('user.username')->label('Created by'),
                TextEntry::make('created_at')->dateTime(),
                TextEntry::make('updated_at')->dateTime(),
            ])->collapsed(),
        ]);
        */
    }
}
