<?php

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\GrnResource\GrnResource;
use App\Filament\Resources\InventoryResource\InventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

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
                ->url(fn (): string => GrnResource::getUrl('create')),
        ];
    }

    public function getMaxContentWidth(): MaxWidth | string | null
    {
        return MaxWidth::Full;
    }
}
