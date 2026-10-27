<?php

namespace App\Filament\Resources\SalesInfoResource\Pages;

use App\Filament\Resources\SalesInfoResource\SalesInfoResource;
use App\Models\Order;
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
                ->url(fn (Order $record): string => route('orders.receipt', ['order' => $record->id]))
                ->openUrlInNewTab(),
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
