<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource\OrderResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Create Order')
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    public function getTabs(): array
    {
        $counts = Order::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalCount = (int) $counts->sum();

        return [
            'all' => Tab::make('All Orders')
                ->badge($totalCount)
                ->icon('heroicon-o-squares-2x2'),
            'processing' => Tab::make('Processing')
                ->icon('heroicon-o-clock')
                ->badge((int) ($counts['Processing'] ?? 0))
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Processing')),
            'packaging' => Tab::make('Packaging')
                ->icon('heroicon-o-cube')
                ->badge((int) ($counts['Packaging'] ?? 0))
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Packaging')),
            'shipping' => Tab::make('Shipping')
                ->icon('heroicon-o-truck')
                ->badge((int) ($counts['Shipping'] ?? 0))
                ->badgeColor('primary')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Shipping')),
            'delivered' => Tab::make('Delivered')
                ->icon('heroicon-o-check-circle')
                ->badge((int) ($counts['Delivered'] ?? 0))
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Delivered')),
        ];
    }
}
