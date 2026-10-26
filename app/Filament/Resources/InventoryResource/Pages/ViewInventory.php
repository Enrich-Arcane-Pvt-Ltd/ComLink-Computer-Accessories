<?php

declare(strict_types=1);

namespace App\Filament\Resources\InventoryResource\Pages;

use App\Filament\Resources\InventoryResource\InventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;

class ViewInventory extends ViewRecord
{
    protected static string $resource = InventoryResource::class;

    protected static string $view = 'filament.pages.view-inventory';

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
