<?php

namespace App\Filament\Resources\ProductSpecificationResource\Pages;

use App\Filament\Resources\ProductSpecificationResource\ProductSpecificationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductSpecifications extends ListRecords
{
    protected static string $resource = ProductSpecificationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Add Specification Attributes')
                ->icon('heroicon-m-plus')
                ->slideOver(),
        ];
    }
}
