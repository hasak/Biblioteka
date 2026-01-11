<?php

namespace App\Filament\Resources\Books\Schemas;

use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Storage;
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
                        TextEntry::make('title')->size(TextSize::Large)->weight(FontWeight::Bold),
                        TextEntry::make('author'),
                        Grid::make(3)->schema([
                            TextEntry::make('series.title')->label('Series')->visible(fn ($record) => $record->series != null)->columnSpan(2),
                            TextEntry::make('part_number')->label('Part')->visible(fn ($record) => $record->part_number != null || $record->series != null),
                        ]),
                        Grid::make(3)->schema([
                            TextEntry::make('genre.name')->label('Genre'),
                            TextEntry::make('language.name')->label('Language'),
                            TextEntry::make('year'),
                        ]),
                        TextEntry::make('isbn')->label('ISBN')->copyable(),
                    ]),
                    Grid::make(12)->schema([
                        Section::make('Shelf position')->schema([
                            ShelfPositionEntry::make('shelf_position')->hiddenLabel(),
                        ])->columnSpan(4),
                        Section::make('Purchase')->schema([
                            TextEntry::make('purchased_in')
                                ->hiddenLabel()
                                ->state(fn ($record) => $record->purchased_city ? $record->purchased_city.", ".$record->purchasedCountry->name : $record->purchasedCountry->name)
                                ->visible(fn ($record) => $record->purchasedCountry != null),
                            TextEntry::make('purchased_date')
                                ->hiddenLabel()
                                ->date('j. n. Y.'),
                        ])->columnSpan(4),
                        Grid::make(1)->schema([
                            Section::make('Is read')->schema([
                                TextEntry::make('is_read')
                                    ->date(fn ($record) => $record->is_read ? ($record->read_date ? 'j. n. Y.' : '') : '')
                                    ->icon(fn ($record) => $record->is_read ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedXCircle)
                                    ->iconColor(fn ($record) => $record->is_read ? 'success' : 'danger')
                                    ->state(fn ($record) => $record->is_read ? ($record->read_date ?? '1999-06-30') : '1999-06-30')
                                    ->hiddenLabel(),
                            ])->columnSpanFull(),
                            Section::make('Meta')->schema([
                                TextEntry::make('user.username')->label('Created by'),
                                TextEntry::make('created_at')->dateTime(),
                                TextEntry::make('updated_at')->dateTime(),
                            ])->columnSpanFull()->collapsed(),
                        ])->columnSpan(4),
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
