<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Disable infolist to render the full ProductForm schema in read-only mode,
     * ensuring the view page layout exactly mirrors the edit option view.
     */
    public function hasInfolist(): bool
    {
        return false;
    }
}
