<?php

namespace App\Filament\Resources\InventoryProductResource\Pages;

use App\Filament\Resources\InventoryProductResource\InventoryProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventoryProduct extends CreateRecord
{
    protected static string $resource = InventoryProductResource::class;

    protected static ?string $title = 'Add Product';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
