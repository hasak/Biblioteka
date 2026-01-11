<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class Dashboard extends Page
{
    protected string $view = 'filament.pages.dashboard';
    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedBuildingLibrary;
}
