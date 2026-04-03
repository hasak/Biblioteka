<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Scan extends Page
{
    protected string $view = 'filament.pages.scan';
    protected static string|null|\BackedEnum $navigationIcon = Heroicon::OutlinedCamera;
}
