<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\InventoryResource\InventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('add_grn')
                ->label('Receive Stock (Add GRN)')
                ->icon('heroicon-o-plus')
                ->color('primary')
                ->url('/admin/grns/create'),
        ];
    }
}
