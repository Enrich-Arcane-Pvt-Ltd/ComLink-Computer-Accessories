<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['images']) && is_array($data['images'])) {
            $data['images'] = array_values(array_filter($data['images']));
        }

        unset($data['image_1'], $data['image_2'], $data['image_3'], $data['image_4'], $data['discount_percentage']);

        return $data;
    }

    protected function afterSave(): void
    {
        $categories = array_values(array_filter([
            $this->data['category_id'] ?? null,
            $this->data['sub_category_id'] ?? null,
        ]));

        if (! empty($categories)) {
            $this->record->categories()->sync($categories);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
