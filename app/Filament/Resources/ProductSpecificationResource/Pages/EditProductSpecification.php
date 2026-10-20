<?php

namespace App\Filament\Resources\ProductSpecificationResource\Pages;

use App\Filament\Resources\ProductSpecificationResource\ProductSpecificationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;

class EditProductSpecification extends EditRecord
{
    protected static string $resource = ProductSpecificationResource::class;

    public function getMaxContentWidth(): MaxWidth | string | null
    {
        return MaxWidth::Full;
    }

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
