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

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('create_grn')
                ->label(fn (): string => $this->record->grns()->exists() ? 'GRN Done' : 'Create GRN')
                ->icon(fn (): string => $this->record->grns()->exists() ? 'heroicon-o-check-circle' : 'heroicon-o-document-plus')
                ->color(fn (): string => $this->record->grns()->exists() ? 'gray' : 'success')
                ->tooltip(fn (): string => $this->record->grns()->exists()
                        ? 'GRN already created: '.$this->record->grns()->first()->grn_number.' (Click to view)'
                        : 'Create Goods Received Note from this Purchase Order'
                )
                ->url(fn (): string => $this->record->grns()->exists()
                        ? GrnResource::getUrl('view', ['record' => $this->record->grns()->first()->id])
                        : GrnResource::getUrl('create', ['purchase_order_id' => $this->record->id])
                ),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
