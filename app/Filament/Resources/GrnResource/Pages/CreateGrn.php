<?php

namespace App\Filament\Resources\GrnResource\Pages;

use App\Filament\Resources\GrnResource\GrnResource;
use App\Models\Grn;
use App\Models\PurchaseOrder;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Str;

class CreateGrn extends CreateRecord
{
    protected static string $resource = GrnResource::class;

    protected static ?string $title = 'Add GRN (Goods Received Note)';

    public function getMaxContentWidth(): MaxWidth | string | null
    {
        return MaxWidth::Full;
    }

    protected function fillForm(): void
    {
        $poId = request()->query('purchase_order_id');
        if ($poId) {
            $po = PurchaseOrder::with('items')->find($poId);
            if ($po) {
                $itemsData = [];
                foreach ($po->items as $item) {
                    $itemsData[(string) Str::uuid()] = [
                        'inventory_product_id' => $item->inventory_product_id,
                        'product_code' => $item->product_code,
                        'product_name' => $item->product_name,
                        'product_condition' => $item->product_condition ?: 'Brandnew',
                        'product_quantity' => (int) ($item->product_quantity ?: 1),
                        'price_per_unit' => 0.00,
                        'total' => 0.00,
                    ];
                }

                $this->callHook('beforeFill');

                $this->form->fill([
                    'grn_number' => Grn::generateGrnNumber(),
                    'supplier_id' => $po->supplier_id,
                    'purchase_order_id' => $po->id,
                    'sub_total' => 0.00,
                    'created_at' => now(),
                    'items' => $itemsData,
                ]);

                $this->callHook('afterFill');
                return;
            }
        }

        parent::fillForm();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calculate sub total from items if not already set
        $subTotal = 0;
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as &$item) {
                $price = (float) ($item['price_per_unit'] ?? 0);
                $qty = (int) ($item['product_quantity'] ?? 1);
                $item['total'] = round($price * $qty, 2);
                $subTotal += $item['total'];
            }
        }
        $data['sub_total'] = round($subTotal, 2);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
