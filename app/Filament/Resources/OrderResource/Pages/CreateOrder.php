<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource\OrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $items = $data['items'] ?? [];
        $subtotal = 0.0;
        foreach ($items as $item) {
            $subtotal += (float) ($item['total_price'] ?? 0);
        }
        $data['subtotal'] = round($subtotal, 2);
        $data['shipping_fee'] = 0.00;
        $data['total_amount'] = round($subtotal, 2);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
