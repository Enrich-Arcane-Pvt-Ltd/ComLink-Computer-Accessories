<?php

namespace App\Filament\Resources\SalesInfoResource\Pages;

use App\Filament\Resources\SalesInfoResource\SalesInfoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSalesInfo extends EditRecord
{
    protected static string $resource = SalesInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_receipt')
                ->label('Download Receipt')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->url(fn (): string => route('orders.receipt', ['order' => $this->getRecord()->id]))
                ->openUrlInNewTab(),
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()->label('Save Changes'),
            $this->getCancelFormAction()->label('Cancel'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
