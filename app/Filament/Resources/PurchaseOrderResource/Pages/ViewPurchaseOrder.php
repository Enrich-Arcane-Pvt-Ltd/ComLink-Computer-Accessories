<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\GrnResource\GrnResource;
use App\Filament\Resources\PurchaseOrderResource\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\MaxWidth;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    public function getMaxContentWidth(): MaxWidth | string | null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_grn')
                ->label('Create GRN')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->url(fn (): string => GrnResource::getUrl('create', ['purchase_order_id' => $this->record->id])),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
