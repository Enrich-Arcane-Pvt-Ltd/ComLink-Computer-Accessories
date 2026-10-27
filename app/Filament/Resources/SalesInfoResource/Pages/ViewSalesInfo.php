<?php

namespace App\Filament\Resources\SalesInfoResource\Pages;

use App\Filament\Resources\SalesInfoResource\SalesInfoResource;
use App\Models\Order;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSalesInfo extends ViewRecord
{
    protected static string $resource = SalesInfoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_receipt')
                ->label('Download Receipt')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('info')
                ->url(fn (Order $record): string => route('orders.receipt', ['order' => $record->id]))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
