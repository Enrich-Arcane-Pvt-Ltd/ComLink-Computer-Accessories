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

        if (isset($data['show_quantity'])) {
            $newShow = (int) $data['show_quantity'];
            $oldShow = (int) ($this->record->show_quantity ?? $this->record->quantity ?? 0);
            $diff = $newShow - $oldShow;

            // Adjust available online quantity proportionally by the change in show_quantity
            $currentAvailable = (int) ($this->record->quantity ?? 0);
            $data['quantity'] = max(0, min($newShow, $currentAvailable + $diff));
        }

        // Ensure inventory_stock is synchronized from GRN
        $code = $data['product_code'] ?? $this->record->product_code;
        if ($code) {
            $data['inventory_stock'] = (int) \App\Models\GrnItem::where('product_code', $code)->sum('product_quantity');
        }

        if (empty($this->record->stock_added_count)) {
            $data['stock_added_count'] = (int) ($data['show_quantity'] ?? $data['quantity'] ?? 0);
            $data['stock_added_date'] = $this->record->created_at ?? now();
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
