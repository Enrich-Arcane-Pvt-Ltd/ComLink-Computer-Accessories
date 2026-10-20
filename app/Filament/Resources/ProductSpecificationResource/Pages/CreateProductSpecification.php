<?php

namespace App\Filament\Resources\ProductSpecificationResource\Pages;

use App\Filament\Resources\ProductSpecificationResource\ProductSpecificationResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\MaxWidth;

class CreateProductSpecification extends CreateRecord
{
    protected static string $resource = ProductSpecificationResource::class;

    protected static ?string $title = '+ Add Specification Attributes';

    public function getMaxContentWidth(): MaxWidth | string | null
    {
        return MaxWidth::Full;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
