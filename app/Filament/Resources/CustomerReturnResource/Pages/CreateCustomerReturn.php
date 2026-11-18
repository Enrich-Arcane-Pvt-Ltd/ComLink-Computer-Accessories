<?php

namespace App\Filament\Resources\CustomerReturnResource\Pages;

use App\Filament\Resources\CustomerReturnResource\CustomerReturnResource;
use App\Models\Order;
use App\Services\ReturnRefundService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCustomerReturn extends CreateRecord
{
    protected static string $resource = CustomerReturnResource::class;

    public function mount(): void
    {
        parent::mount();

        $orderId = request()->query('order_id');
        if ($orderId && ($order = Order::with('items')->find((int) $orderId))) {
            $repeaterItems = [];
            $total = 0.0;
            foreach ($order->items as $item) {
                $returnable = $item->returnable_quantity;
                if ($returnable <= 0) {
                    continue;
                }
                $repeaterItems[] = [
                    'order_item_id' => $item->id,
                    'product_name' => $item->product_name . ($item->color ? " ({$item->color})" : ''),
                    'max_returnable' => $returnable,
                    'quantity' => $returnable,
                    'unit_price' => (float) $item->unit_price,
                    'condition' => 'damaged',
                    'notes' => null,
                ];
                $total += $returnable * (float) $item->unit_price;
            }

            $this->form->fill([
                'order_id' => $order->id,
                'refund_method' => 'cash',
                'reason' => 'Damaged / Defective Product',
                'refund_amount' => round($total, 2),
                'items' => $repeaterItems,
            ]);
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        $order = Order::findOrFail($data['order_id']);
        $itemsData = array_filter($data['items'] ?? [], fn ($row) => (int) ($row['quantity'] ?? 0) > 0);

        if (empty($itemsData)) {
            Notification::make()
                ->title('No items selected for return')
                ->body('Please set a return quantity greater than 0 for at least one item.')
                ->danger()
                ->send();

            $this->halt();
        }

        return app(ReturnRefundService::class)->processCustomerReturn($order, $itemsData, [
            'refund_amount' => $data['refund_amount'] ?? null,
            'refund_method' => $data['refund_method'] ?? 'cash',
            'reason' => $data['reason'] ?? 'Damaged / Defective Product',
            'admin_notes' => $data['admin_notes'] ?? null,
            'status' => 'approved',
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
