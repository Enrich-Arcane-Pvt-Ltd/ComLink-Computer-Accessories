<?php

namespace App\Filament\Resources\InventoryProductResource\Pages;

use App\Filament\Resources\InventoryProductResource\InventoryProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventoryProducts extends ListRecords
{
    protected static string $resource = InventoryProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add products')
                ->icon('heroicon-o-plus'),
        ];
    }
}
