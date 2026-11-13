<?php

namespace App\Filament\Resources\GrnResource\Pages;

use App\Filament\Resources\GrnResource\GrnResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\MaxWidth;

class EditGrn extends EditRecord
{
    protected static string $resource = GrnResource::class;

    public function getMaxContentWidth(): MaxWidth|string|null
    {
        return MaxWidth::Full;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->url(fn (): string => route('admin.grn.print', $this->record->id))
                ->openUrlInNewTab(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $grnType = $data['grn_type'] ?? (! empty($data['purchase_order_id']) ? 'po' : 'direct');
        $data['grn_type'] = $grnType;

        if ($grnType === 'direct' || empty($data['purchase_order_id'])) {
            $data['purchase_order_id'] = null;
            if (empty($data['direct_po_number'])) {
                $data['direct_po_number'] = $this->record->direct_po_number ?: \App\Models\Grn::generateDirectPoNumber();
            }
        } else {
            $data['direct_po_number'] = null;
        }

        if (isset($data['items']) && is_array($data['items']) && count($data['items']) > 0) {
            $subTotal = 0;
            foreach ($data['items'] as &$item) {
                $price = (float) ($item['price_per_unit'] ?? 0);
                $qty = (int) ($item['product_quantity'] ?? 1);
                $item['total'] = round($price * $qty, 2);
                $subTotal += $item['total'];
            }
            $data['sub_total'] = round($subTotal, 2);
        } elseif (! isset($data['sub_total']) || (float) $data['sub_total'] <= 0) {
            $existingTotal = (float) $this->record->items()->sum('total');
            if ($existingTotal > 0) {
                $data['sub_total'] = round($existingTotal, 2);
            }
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $this->record->recalculateSubTotal();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
