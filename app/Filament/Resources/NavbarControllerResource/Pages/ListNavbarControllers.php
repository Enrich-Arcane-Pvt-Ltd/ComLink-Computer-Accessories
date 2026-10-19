<?php

namespace App\Filament\Resources\NavbarControllerResource\Pages;

use App\Filament\Resources\NavbarControllerResource\NavbarControllerResource;
use Filament\Resources\Pages\ListRecords;

class ListNavbarControllers extends ListRecords
{
    protected static string $resource = NavbarControllerResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
