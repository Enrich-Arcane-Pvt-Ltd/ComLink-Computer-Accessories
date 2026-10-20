<?php

namespace App\Filament\Resources\InventoryProductResource\Pages;

use App\Filament\Resources\InventoryProductResource\InventoryProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditInventoryProduct extends EditRecord
{
    protected static string $resource = InventoryProductResource::class;

    protected static ?string $title = 'Edit Product';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
