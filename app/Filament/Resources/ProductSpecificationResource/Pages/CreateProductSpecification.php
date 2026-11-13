<?php

namespace App\Filament\Resources\ProductSpecificationResource\Pages;

use App\Filament\Resources\ProductSpecificationResource\ProductSpecificationResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Database\Eloquent\Model;

class CreateProductSpecification extends CreateRecord
{
    protected static string $resource = ProductSpecificationResource::class;

    protected static ?string $title = '+ Add Specification Attributes';

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $query = static::getModel()::where('category_id', $data['category_id']);
        if (! empty($data['sub_category_id'])) {
            $query->where('sub_category_id', $data['sub_category_id']);
        } else {
            $query->whereNull('sub_category_id');
        }

        $existing = $query->first();
        if ($existing) {
            $existing->update($data);

            return $existing;
        }

        return static::getModel()::create($data);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Specification attributes saved successfully';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
