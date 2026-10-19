<?php

namespace App\Filament\Resources\ProductSpecificationResource\Pages;

use App\Filament\Resources\ProductSpecificationResource\ProductSpecificationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductSpecification extends CreateRecord
{
    protected static string $resource = ProductSpecificationResource::class;

    protected static ?string $title = '+ Add Specification Attributes';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
