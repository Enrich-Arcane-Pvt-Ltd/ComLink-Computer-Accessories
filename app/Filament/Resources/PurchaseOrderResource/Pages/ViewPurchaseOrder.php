<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_grn')
                ->label('Create GRN')
                ->icon('heroicon-o-document-plus')
                ->color('success')
                ->url(fn (): string => url('/admin/grns/create?purchase_order_id=' . $this->record->id)),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
